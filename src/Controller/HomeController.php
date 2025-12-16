<?php
namespace App\Controller;

use App\Entity\Palabra;
use App\Repository\PalabraRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(PalabraRepository $palabraRepo): Response
    {
        $palabras = $palabraRepo->findBy([], ['fechaCreacion' => 'DESC']);

        return $this->render('page/index.html.twig', [
            'palabras' => $palabras,
        ]);
    }
}
