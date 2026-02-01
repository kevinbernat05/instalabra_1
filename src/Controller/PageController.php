<?php

namespace App\Controller;

use App\Entity\Palabra;
use App\Entity\Valoracion;
use App\Form\PalabraType;
use App\Repository\PalabraRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Entity\Comentario;
use App\Entity\Seguimiento;
use App\Entity\Usuario;
use App\Form\ComentarioType;
use App\Repository\SeguimientoRepository;
use App\Repository\UsuarioRepository;
use App\Repository\ValoracionRepository;



use App\Service\TimeService;

final class PageController extends AbstractController
{
    private TimeService $timeService;

    public function __construct(TimeService $timeService)
    {
        $this->timeService = $timeService;
    }

    // ----------------- Página principal: mostrar y publicar palabras -----------------
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        PalabraRepository $palabraRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $palabra = new Palabra();

        // Crear formulario
        $form = $this->createForm(PalabraType::class, $palabra);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Asignar usuario y fecha automáticamente
            $palabra->setUsuario($this->getUser());
            $palabra->setFechaCreacion($this->timeService->getNow());

            // Guardar en DB
            $entityManager->persist($palabra);
            $entityManager->flush();

            $this->addFlash('success', '¡Palabra publicada!');
            return $this->redirectToRoute('app_home');
        }

        $filter = $request->query->get('filter', 'foryou');
        /** @var Usuario|null $user */
        $user = $this->getUser();

        if ($filter === 'following' && $user) {
            // Obtener IDs de usuarios seguidos
            $followedUsers = [];
            foreach ($user->getSeguimientosQueHace() as $seguimiento) {
                $followedUsers[] = $seguimiento->getSeguido();
            }
            // Incluirse a uno mismo también? Típicamente sí o no, depende. 
            // Vamos a incluir solo seguidos.

            $palabras = $palabraRepository->findBy(
                ['usuario' => $followedUsers],
                ['fechaCreacion' => 'DESC']
            );
        } else {
            $palabras = $palabraRepository->findAllActive();
        }

        // --- Ranking Logic for Index ---
        $now = $this->timeService->getNow();
        $startDate = (clone $now)->modify('-1 day');
        $topPalabras = $palabraRepository->findTopByLikes(5, $startDate);
        $maxLikes = 1;
        if (!empty($topPalabras)) {
            $maxLikes = $topPalabras[0]['likesCount'];
            if ($maxLikes == 0)
                $maxLikes = 1;
        }
        // -------------------------------


        return $this->render('page/index.html.twig', [
            'form' => $form->createView(),
            'palabras' => $palabras,
            'currentFilter' => $filter,
            'topWords' => $topPalabras,
            'maxLikes' => $maxLikes,
            // Pass monthly top words for initial render if we want, but template only showed "Trends of the day" and "Trends of the month"
            // Wait, the template logic for "month" was using `topWords` again?
            // Line 45 in index.html.twig: `for palabra in topWords|slice(0,3)` in `rank-month` box.
            // IT WAS USING THE SAME VARIABLE `topWords` (derived from daily -1 day) FOR BOTH!
            // I should fix that in the controller too while I am here, or at least be aware of it.
            // The prompt says "trends of the day" and "trends of the month".
            // See PageController line 86-87: $startDate = (clone $now)->modify('-1 day'); $topPalabras = ...
            // And index.html.twig reuses it.
            // I will fix the initial render to use correct monthly data too in the index method.
            'topWordsMonth' => $palabraRepository->findTopByLikes(5, (clone $this->timeService->getNow())->modify('-1 month'))
        ]);
    }

    #[Route('/api/trending', name: 'api_trending')]
    public function trendingApi(PalabraRepository $palabraRepository): JsonResponse
    {
        $now = $this->timeService->getNow();
        $daily = $palabraRepository->findTopByLikes(3, (clone $now)->modify('-1 day'));
        $monthly = $palabraRepository->findTopByLikes(3, (clone $now)->modify('-1 month'));

        $format = function ($list) {
            $formatted = [];
            $max = 0;
            foreach ($list as $item) {
                if ($item['likesCount'] > $max)
                    $max = $item['likesCount'];
            }
            if ($max == 0)
                $max = 1;

            foreach ($list as $item) {
                $formatted[] = [
                    'id' => $item['palabraEntity']->getId(),
                    'palabra' => $item['palabraEntity']->getPalabra(),
                    'likes' => $item['likesCount'],
                    'max' => $max
                ];
            }
            return $formatted;
        };

        return $this->json([
            'daily' => $format($daily),
            'monthly' => $format($monthly)
        ]);
    }

    // ----------------- Toggle Like / Quitar Like -----------------
    #[Route('/palabra/like/{id}', name: 'palabra_like_toggle')]
    public function toggleLike(
        Request $request,
        Palabra $palabra,
        EntityManagerInterface $entityManager
    ): Response {
        $usuario = $this->getUser();
        $valoracionRepo = $entityManager->getRepository(Valoracion::class);

        // Buscar si el usuario ya tiene un like en esta palabra
        $valoracion = $valoracionRepo->findOneBy([
            'usuario' => $usuario,
            'palabra' => $palabra
        ]);

        if (!$valoracion) {
            // No existe → crear like
            $valoracion = new Valoracion();
            $valoracion->setUsuario($usuario);
            $valoracion->setPalabra($palabra);
            $valoracion->setLikeActiva(true);
            $valoracion->setFechaCreacion($this->timeService->getNow());

            $entityManager->persist($valoracion);
        } else {
            // Ya existe → alternar like
            $valoracion->setLikeActiva(!$valoracion->isLikeActiva());
            // Opcional: actualizar fecha si se reactiva? No, mejor mantener original o actualizar?
            // "modificar la fecha que detecta el sistema". Si reactivo like en el pasado/futuro, 
            // no suele cambiar la fecha de creación del like original, pero si es un toggle, 
            // a veces se considera "nuevo like". 
            // Mantendremos la fecha original de creación del registro Valoracion.
        }

        $entityManager->flush();

        if ($request->isXmlHttpRequest() || $request->headers->get('Accept') === 'application/json') {
            $count = 0;
            foreach ($palabra->getValoraciones() as $v) {
                if ($v->isLikeActiva())
                    $count++;
            }
            return $this->json([
                'liked' => $valoracion->isLikeActiva(),
                'count' => $count
            ]);
        }

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/perfil', name: 'app_perfil')]
    public function perfil(PalabraRepository $palabraRepository): Response
    {
        $usuario = $this->getUser();

        if (!$usuario) {
            return $this->redirectToRoute('app_login');
        }

        // Traer las palabras de este usuario ordenadas por fecha descendente
        $palabras = $palabraRepository->findBy(
            ['usuario' => $usuario],
            ['fechaCreacion' => 'DESC']
        );

        return $this->render('page/perfil.html.twig', [
            'usuario' => $usuario,
            'palabras' => $palabras,
        ]);
    }
    #[Route('/ranking', name: 'app_ranking')]
    public function ranking(
        Request $request,
        PalabraRepository $palabraRepository,
        UsuarioRepository $usuarioRepository
    ): Response {
        $period = $request->query->get('period', 'daily'); // daily, weekly, monthly

        $now = $this->timeService->getNow();
        // Clone to avoid modifying $now object if it's reused, though getNow returns new instance usually.
        // It's safer to clone.
        $startDate = match ($period) {
            'weekly' => (clone $now)->modify('-1 week'),
            'monthly' => (clone $now)->modify('-1 month'),
            default => (clone $now)->modify('-1 day'),
        };

        $topPalabras = $palabraRepository->findTopByLikes(10, $startDate);
        $topUsuarios = $usuarioRepository->findTopUsersByFollowers(10, $startDate);

        //Para calcular la barra que se va a rellenar con dependiendo del num de likes
        $maxLikes = 1;
        if (!empty($topPalabras)) {
            $maxLikes = $topPalabras[0]['likesCount'];
            if ($maxLikes == 0) {
                $maxLikes = 1;
            }
        }

        return $this->render('page/ranking.html.twig', [
            'palabras' => $topPalabras,
            'usuarios' => $topUsuarios,
            'period' => $period,
            'debugDate' => $now, // Pass current simulated date for UI context if needed
            'maxLikes' => $maxLikes
        ]);
    }

    #[Route('/usuario/{id}', name: 'app_usuario_perfil')]
    public function userProfile(
        Usuario $usuario,
        PalabraRepository $palabraRepository,
        SeguimientoRepository $seguimientoRepository
    ): Response {
        $palabras = $palabraRepository->findBy(
            ['usuario' => $usuario],
            ['fechaCreacion' => 'DESC']
        );

        $currentUser = $this->getUser();
        $isFollowing = false;

        if ($currentUser && $currentUser !== $usuario) {
            $isFollowing = $seguimientoRepository->isFollowing($currentUser, $usuario);
        }

        $followersCount = $seguimientoRepository->countFollowers($usuario);
        $followingCount = $seguimientoRepository->countFollowing($usuario);

        return $this->render('page/public_profile.html.twig', [
            'usuario' => $usuario,
            'palabras' => $palabras,
            'isFollowing' => $isFollowing,
            'followersCount' => $followersCount,
            'followingCount' => $followingCount
        ]);
    }

    #[Route('/usuario/{id}/follow', name: 'app_usuario_follow')]
    public function toggleFollow(
        Usuario $usuarioToFollow,
        EntityManagerInterface $entityManager,
        SeguimientoRepository $seguimientoRepository,
        Request $request
    ): Response {
        /** @var Usuario $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        if ($currentUser === $usuarioToFollow) {
            if ($request->isXmlHttpRequest() || $request->headers->get('Accept') === 'application/json') {
                return $this->json(['error' => 'No puedes seguirte a ti mismo'], 400);
            }
            return $this->redirect($request->headers->get('referer'));
        }

        $existingFollow = $entityManager->getRepository(Seguimiento::class)->findOneBy([
            'seguidor' => $currentUser,
            'seguido' => $usuarioToFollow
        ]);

        if ($existingFollow) {
            $entityManager->remove($existingFollow);
        } else {
            $seguimiento = new Seguimiento();
            $seguimiento->setSeguidor($currentUser);
            $seguimiento->setSeguido($usuarioToFollow);
            $seguimiento->setFechaSeguimiento(new \DateTime());
            $entityManager->persist($seguimiento);
        }



        $entityManager->flush();

        if ($request->isXmlHttpRequest() || $request->headers->get('Accept') === 'application/json') {
            return $this->json([
                'following' => !$existingFollow, // If it existed, we removed it (false). If not, we added it (true).
                'followersCount' => $seguimientoRepository->countFollowers($usuarioToFollow)
            ]);
        }

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/palabra/{id}/comentar', name: 'palabra_comentar', methods: ['POST'])]
    public function comentar(Palabra $palabra, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $usuario = $this->getUser();
        if (!$usuario) {
            return $this->redirectToRoute('app_login');
        }

        $texto = $request->request->get('comentario_texto'); // input name="comentario_texto"
        if ($texto) {
            $comentario = new Comentario();
            $comentario->setUsuario($usuario);
            $comentario->setPalabra($palabra);
            $comentario->setTexto($texto);
            $comentario->setFechaCreacion(new \DateTime());

            $entityManager->persist($comentario);
            $entityManager->flush();
        }

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/palabra/{id}/delete', name: 'palabra_delete', methods: ['POST'])]
    public function deletePalabra(
        Request $request,
        Palabra $palabra,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $usuario = $this->getUser();

        // Verificar que el usuario sea el dueño de la palabra
        // Verificar que el usuario sea el dueño de la palabra o sea admin
        if (!$usuario || ($usuario !== $palabra->getUsuario() && !$this->isGranted('ROLE_ADMIN'))) {
            throw $this->createAccessDeniedException('No tienes permiso para eliminar esta publicación.');
        }

        // Token CSRF opcional pero recomendado. Por simplicidad en esta iteración y rapidez,
        // confío en que el botón delete será un form con POST.
        // Si se desea CSRF explícito: if ($this->isCsrfTokenValid('delete'.$palabra->getId(), $request->request->get('_token')))
        if ($this->isCsrfTokenValid('delete' . $palabra->getId(), $request->request->get('_token'))) {
            //$entityManager->remove($palabra);
            $palabra->setDeletedAt(new \DateTime());
            $entityManager->flush();
            $this->addFlash('success', 'Publicación eliminada correctamente.');
        } else {
            $this->addFlash('error', 'Token inválido, no se pudo eliminar.');
        }

        // Redirigir a la home o perfil
        return $this->redirectToRoute('app_home');
    }

    #[Route('/comentario/{id}/delete', name: 'comentario_delete', methods: ['POST'])]
    public function deleteComentario(
        Request $request,
        Comentario $comentario,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $usuario = $this->getUser();

        if (!$usuario || ($usuario !== $comentario->getUsuario() && !$this->isGranted('ROLE_ADMIN'))) {
            throw $this->createAccessDeniedException('No tienes permiso para eliminar este comentario.');
        }

        if ($this->isCsrfTokenValid('delete' . $comentario->getId(), $request->request->get('_token'))) {
            //$entityManager->remove($comentario);
            $comentario->setDeletedAt(new \DateTime());
            $entityManager->flush();
            $this->addFlash('success', 'Comentario eliminado.');
        } else {
            $this->addFlash('error', 'Token inválido.');
        }

        return $this->redirect($request->headers->get('referer'));
    }


    public function rankingWidget(
        PalabraRepository $palabraRepository,
        UsuarioRepository $usuarioRepository
    ): Response {
        $now = $this->timeService->getNow();
        $startDate = (clone $now)->modify('-1 day');

        $topPalabras = $palabraRepository->findTopByLikes(5, $startDate);

        // Calculate max likes for progress bar
        $maxLikes = 1;
        if (!empty($topPalabras)) {
            $maxLikes = $topPalabras[0]['likesCount'];
            if ($maxLikes == 0)
                $maxLikes = 1;
        }

        // Get random users or top users as suggestions
        $suggestedUsers = $usuarioRepository->findTopUsersByFollowers(3);

        $followingIds = [];
        $user = $this->getUser();
        if ($user) {
            /** @var \App\Entity\Usuario $user */
            foreach ($user->getSeguimientosQueHace() as $seguimiento) {
                $followingIds[] = $seguimiento->getSeguido()->getId();
            }
        }

        return $this->render('page/sidebar_right.html.twig', [
            // 'topWords' => $topPalabras, // Movido al index
            // 'maxLikes' => $maxLikes,    // Movido al index
            'suggestedUsers' => $suggestedUsers,
            'followingIds' => $followingIds
        ]);
    }

    #[Route('/palabra/{id}', name: 'palabra_show')]
    public function show(Palabra $palabra): Response
    {
        return $this->render('page/palabra.html.twig', [
            'palabra' => $palabra,
        ]);
    }

    #[Route('/buscar', name: 'app_search')]
    public function search(Request $request, EntityManagerInterface $em): Response
    {
        $query = $request->query->get('q');
        $currentFilter = $request->query->get('filter') ?? 'usuarios'; // <-- por defecto Usuarios

        $palabras = $query ? $em->getRepository(Palabra::class)
            ->createQueryBuilder('p')
            ->join('p.usuario', 'u')
            ->where('(p.palabra LIKE :q OR p.definicion LIKE :q)')
            ->andWhere('(u.isBlocked = :blocked OR u.isBlocked IS NULL)')
            ->setParameter('q', '%' . $query . '%')
            ->setParameter('blocked', false)
            ->getQuery()
            ->getResult() : [];

        $usuarios = $query ? $em->getRepository(Usuario::class)
            ->createQueryBuilder('u')
            ->where('u.nombre LIKE :q')
            ->andWhere('(u.isBlocked = :blocked OR u.isBlocked IS NULL)')
            ->setParameter('q', '%' . $query . '%')
            ->setParameter('blocked', false)
            ->getQuery()
            ->getResult() : [];

        return $this->render('page/results.html.twig', [
            'palabras' => $palabras,
            'usuarios' => $usuarios,
            'query' => $query,
            'currentFilter' => $currentFilter,
        ]);
    }
}
