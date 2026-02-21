<?php

namespace App\Controller;

use App\Entity\Palabra;
use App\Entity\Comentario;
use App\Repository\PalabraRepository;
use App\Repository\ComentarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/borrados')]
#[IsGranted('ROLE_ADMIN')]
class AdminTrashController extends AbstractController
{
    #[Route('/', name: 'admin_trash_index')]
    public function index(PalabraRepository $palabraRepository, ComentarioRepository $comentarioRepository): Response
    {
        // 14 days logic
        $dateLimit = new \DateTime();
        $dateLimit->modify('-14 days');

        $deletedPalabras = $palabraRepository->createQueryBuilder('p')
            ->where('p.deletedAt IS NOT NULL')
            ->andWhere('p.deletedAt >= :dateLimit')
            ->setParameter('dateLimit', $dateLimit)
            ->orderBy('p.deletedAt', 'DESC')
            ->getQuery()
            ->getResult();

        $deletedComentarios = $comentarioRepository->createQueryBuilder('c')
            ->where('c.deletedAt IS NOT NULL')
            ->andWhere('c.deletedAt >= :dateLimit')
            ->setParameter('dateLimit', $dateLimit)
            ->orderBy('c.deletedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/trash.html.twig', [
            'palabras' => $deletedPalabras,
            'comentarios' => $deletedComentarios
        ]);
    }

    #[Route('/palabra/{id}/restore', name: 'admin_trash_restore_palabra')]
    public function restorePalabra(Palabra $palabra, EntityManagerInterface $em): Response
    {
        $palabra->setDeletedAt(null);
        $em->flush();
        $this->addFlash('success', 'Publicación restaurada.');
        return $this->redirectToRoute('admin_trash_index');
    }

    #[Route('/comentario/{id}/restore', name: 'admin_trash_restore_comentario')]
    public function restoreComentario(Comentario $comentario, EntityManagerInterface $em): Response
    {
        $comentario->setDeletedAt(null);
        $em->flush();
        $this->addFlash('success', 'Comentario restaurado.');
        return $this->redirectToRoute('admin_trash_index');
    }
}
