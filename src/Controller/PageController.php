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
            // Asignar usuario y fecha
            $palabra->setUsuario($this->getUser());
            $palabra->setFechaCreacion(new \DateTime());

            // Guardar en DB
            $entityManager->persist($palabra);
            $entityManager->flush();

            $this->addFlash('success', '¡Palabra publicada!');
            return $this->redirectToRoute('app_home');
        }

        // Traer todas las palabras ordenadas por fecha
        $palabras = $palabraRepository->findBy([], ['fechaCreacion' => 'DESC']);

        return $this->render('page/index.html.twig', [
            'form' => $form->createView(),
            'palabras' => $palabras,
        ]);
    }

    // ----------------- Toggle Like / Quitar Like -----------------
    #[Route('/palabra/like/{id}', name: 'palabra_like_toggle')]
    public function toggleLike(
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

        return $this->redirectToRoute('app_home');
    }
}
