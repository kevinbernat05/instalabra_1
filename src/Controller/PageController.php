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
use App\Form\ComentarioType;


final class PageController extends AbstractController
{
    // ----------------- Página principal: mostrar y publicar palabras -----------------
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request, 
        PalabraRepository $palabraRepository, 
        EntityManagerInterface $entityManager
    ): Response
    {
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
    public function toggleLike(Request $request,
        Palabra $palabra, 
        EntityManagerInterface $entityManager
    ): RedirectResponse
    {
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
    public function ranking(PalabraRepository $palabraRepository): Response
    {
        $palabras = $palabraRepository->findTopByLikes(10); // top 10

        return $this->render('page/ranking.html.twig', [
            'palabras' => $palabras,
        ]);
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
