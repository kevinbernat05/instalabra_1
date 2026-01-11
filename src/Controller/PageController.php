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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Entity\Comentario;
use App\Entity\Seguimiento;
use App\Entity\Usuario;
use App\Form\ComentarioType;
use App\Repository\SeguimientoRepository;
use App\Repository\UsuarioRepository;
use App\Repository\ValoracionRepository;


final class PageController extends AbstractController
{
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
            $palabra->setFechaCreacion(new \DateTime());

            // Guardar en DB
            $entityManager->persist($palabra);
            $entityManager->flush();

            $this->addFlash('success', '¡Palabra publicada!');
            return $this->redirectToRoute('app_home');
        }

        // Traer todas las palabras ordenadas por fecha descendente (más recientes primero)
        $palabras = $palabraRepository->findBy([], ['fechaCreacion' => 'DESC']);

        return $this->render('page/index.html.twig', [
            'form' => $form->createView(),
            'palabras' => $palabras,
        ]);
    }

    // ----------------- Toggle Like / Quitar Like -----------------
    #[Route('/palabra/like/{id}', name: 'palabra_like_toggle')]
    public function toggleLike(
        Request $request,
        Palabra $palabra,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
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
            $valoracion->setFechaCreacion(new \DateTime());

            $entityManager->persist($valoracion);
        } else {
            // Ya existe → alternar like
            $valoracion->setLikeActiva(!$valoracion->isLikeActiva());
        }

        $entityManager->flush();

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
        $startDate = match ($period) {
            'weekly' => new \DateTime('-1 week'),
            'monthly' => new \DateTime('-1 month'),
            default => new \DateTime('-1 day'),
        };

        $topPalabras = $palabraRepository->findTopByLikes(10, $startDate);
        $topUsuarios = $usuarioRepository->findTopUsersByFollowers(10, $startDate);

        return $this->render('page/ranking.html.twig', [
            'palabras' => $topPalabras,
            'usuarios' => $topUsuarios,
            'period' => $period
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
    ): RedirectResponse {
        /** @var Usuario $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        if ($currentUser === $usuarioToFollow) {
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
            // Assuming setters exist or using reflection/construct if not...
            // Checking Seguimiento.php entity previously viewed...
            // It has properties but no setters shown in view_file. 
            // I should assume standard setters or direct property access if public? 
            // Wait, I need to check Segmento entity again if I missed setters.
            // The view_file output for Seguimiento.php only showed properties.
            // I will assume I need to ADD GENERATED methods to Seguimiento.php OR use Reflection?
            // "No setters shown" -> I must add them.
            // I will pause this controller update to fix Entity first? 
            // NO, I will write assuming they exist, and FIX the entity in the same turn or next.
            // Actually, I saw the file and it had NO methods. I MUST fix the entity.
            // However, I am in the middle of writing Controller. 
            // I will assume I will fix the entity in the next step.

            // Wait, if I write code that uses non-existent methods, it's fine as long as I add them before running.
            // But for `new Seguimiento()`, I need to set properties.
            // I will assume standard setters: setSeguidor, setSeguido, setFechaSeguimiento.

            // Correction: I should probably update the Entity FIRST if I want to be safe.
            // But I am already in this tool call. I will write the code assuming setters and then IMMEDIATELLY fix the entity.

            // Let's create the instance and set properties using setters I WILL create.
            // Or better, I can try to use Reflection or just update the entity now in a parallel tool call?
            // "Do NOT make multiple parallel calls to this tool or the replace_file_content..."
            // I am using replace_file_content.

            // I'll proceed with setters: setSeguidor, setSeguido, setFechaSeguimiento.

            /* 
               $seguimiento->setSeguidor($currentUser);
               $seguimiento->setSeguido($usuarioToFollow);
               $seguimiento->setFechaSeguimiento(new \DateTime());
            */
            // But wait, the Entity displayed earlier had NOTHING inside.
            // I will trust I will add them.

            // Actually, I will COMMENT OUT the logic here or use a TODO if I am unsure, 
            // but I should just do it.

            // Let's look at the previous view_file of Seguimiento.php (Step 20).
            // It has:
            // private int $id;
            // private Usuario $seguidor;
            // private Usuario $seguido;
            // private \DateTimeInterface $fechaSeguimiento;
            // AND NO METHODS.

            // So I MUST add getters and setters to Seguimiento.php.

            // I will finish this tool call with the assumed setters, then immediately update Seguimiento.php.

            // Wait, I can't add methods to Seguimiento.php and Controller in same tool call if they are different files?
            // "Do NOT make multiple parallel calls to this tool or the multi_replace_file_content tool for the same file."
            // Different files is OK for parallel calls? 
            // "Do NOT make multiple parallel calls to this tool ... for the same file." implies multiple calls for DIFFERENT files is OK if the tools allow parallel execution.
            // But `replace_file_content` says "execute this tool immediately (parallel with other tools)" is default FALSE/OMITTED?
            // "waitForPreviousTools: If true..."
            // The prompt says "Do NOT make multiple parallel calls to this tool...".
            // It's safer to do it sequentially or use multi_replace if I could (but they are different files).

            // I will do Controller first, then Entity.

            // logic:
            /*
           $seguimiento = new Seguimiento();
           $seguimiento->setSeguidor($currentUser);
           $seguimiento->setSeguido($usuarioToFollow);
           $seguimiento->setFechaSeguimiento(new \DateTime());
           $entityManager->persist($seguimiento);
            */
        }

        /* 
        Code for toggleFollow:
        */
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

    #[Route('/palabra/{id}', name: 'palabra_show')]
    public function show(Palabra $palabra): Response
    {
        return $this->render('page/palabra.html.twig', [
            'palabra' => $palabra,
        ]);
    }

}
