<?php
namespace App\Controller;

use App\Entity\Usuario;
use App\Form\RegistroType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


class RegistroController extends AbstractController
{
    #[Route('/register', name:'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $usuario = new Usuario();
        $form = $this->createForm(RegistroType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword($usuario, $usuario->getPassword());
            $usuario->setPassword($hashedPassword);
            $usuario->setFechaRegistro(new \DateTime());

            $em->persist($usuario);
            $em->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registro/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/login', name:'app_login')]
    public function login(): Response
    {
        return $this->render('security/login.html.twig', []);
    }

    #[Route('/logout', name:'app_logout')]
    public function logout(): void {}
}
