<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Palabra;
use App\Entity\Mensaje;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(UsuarioRepository $usuarioRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'users' => $usuarioRepository->findAll(),
        ]);
    }

    #[Route('/user/{id}/toggle-block', name: 'admin_user_toggle_block')]
    public function toggleBlock(Usuario $usuario, EntityManagerInterface $em): Response
    {
        // Prevent blocking self
        if ($usuario === $this->getUser()) {
            $this->addFlash('error', 'You cannot block yourself.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $usuario->setIsBlocked(!$usuario->isBlocked());
        $em->flush();

        $status = $usuario->isBlocked() ? 'blocked' : 'unblocked';
        $this->addFlash('success', "User {$usuario->getNombre()} has been $status.");

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/palabra/{id}/delete', name: 'admin_palabra_delete')]
    public function deletePalabra(Palabra $palabra, EntityManagerInterface $em): Response
    {
        $em->remove($palabra);
        $em->flush();

        $this->addFlash('success', 'Post deleted successfully by admin.');
        
        // Redirect back to where we came from, or dashboard
        return $this->redirectToRoute('app_home'); // Redirect to main feed
    }
}
