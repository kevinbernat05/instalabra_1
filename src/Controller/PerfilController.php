<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;            // 👈 Ruta
use Symfony\Component\HttpFoundation\File\UploadedFile;     // 👈 UploadedFile
use App\Form\PerfilType;                                     // 👈 FormType
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;                     // 👈 EntityManagerInterface   

class PerfilController extends AbstractController
{
    #[Route('/perfil/editar', name: 'perfil_editar')]
    public function editar(Request $request, EntityManagerInterface $em): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        $form = $this->createForm(PerfilType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $foto */
            $foto = $form->get('fotoPerfil')->getData();

            if ($foto) {
                $nombreArchivo = uniqid().'.'.$foto->guessExtension();
                $foto->move(
                    $this->getParameter('perfil_dir'),
                    $nombreArchivo
                );
                $usuario->setFotoPerfil($nombreArchivo); // ahora IDE lo reconoce
            }

            $em->flush();

            $this->addFlash('success', 'Perfil actualizado correctamente');

            return $this->redirectToRoute('app_perfil');
        }

        return $this->render('page/foto.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}



