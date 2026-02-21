<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResetPasswordController extends AbstractController
{
    #[Route('/reset-password', name: 'app_forgot_password_request')]
    public function request(Request $request, UsuarioRepository $usuarioRepository, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $usuarioRepository->findOneBy(['email' => $email]);

            if ($user) {
                $resetToken = bin2hex(random_bytes(32));
                $user->setResetToken($resetToken);
                $user->setResetTokenExpiresAt(new \DateTime('+1 hour'));
                $entityManager->flush();

                // Simulation: In a real app, send email here.
                // For now, we flash the link to the user.
                $resetUrl = $this->generateUrl('app_reset_password', ['token' => $resetToken], UrlGeneratorInterface::ABSOLUTE_URL);
                
                // Using a 'warning' flash just so it stands out and user sees the link clearly for testing
                $this->addFlash('success', 'Email enviado (Simulación). Haz click aquí para resetear: <a href="' . $resetUrl . '">Resetear Contraseña</a>');
            } else {
                // To prevent user enumeration, we might say "If account exists...", but for dev clarity:
                $this->addFlash('error', 'No se encontró un usuario con ese email.');
            }
        }

        return $this->render('reset_password/request.html.twig');
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(
        string $token, 
        Request $request, 
        UsuarioRepository $usuarioRepository, 
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $user = $usuarioRepository->findOneBy(['resetToken' => $token]);

        if (!$user || $user->getResetTokenExpiresAt() < new \DateTime()) {
            $this->addFlash('error', 'El enlace de reseteo es inválido o ha expirado.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $passwordConfirm = $request->request->get('password_confirm');

            if ($password === $passwordConfirm) {
                // Clear token
                $user->setResetToken(null);
                $user->setResetTokenExpiresAt(null);

                // Update password
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);
                
                $entityManager->flush();

                $this->addFlash('success', 'Tu contraseña ha sido actualizada exitosamente.');
                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('error', 'Las contraseñas no coinciden.');
            }
        }

        return $this->render('reset_password/reset.html.twig', [
            'token' => $token
        ]);
    }
}
