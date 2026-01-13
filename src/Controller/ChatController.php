<?php

namespace App\Controller;

use App\Entity\Mensaje;
use App\Entity\Usuario;
use App\Entity\Palabra;
use App\Repository\MensajeRepository;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mensajes')]
class ChatController extends AbstractController
{
    #[Route('/', name: 'app_chat_index')]
    public function index(MensajeRepository $mensajeRepository): Response
    {
        /** @var Usuario $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Obtener conversaciones recientes
        // El repositorio debe implementar findRecentConversations
        $conversations = $mensajeRepository->findRecentConversations($user);

        return $this->render('chat/index.html.twig', [
            'conversations' => $conversations,
        ]);
    }

    #[Route('/{id}', name: 'app_chat_show', requirements: ['id' => '\d+'])]
    public function show(
        Usuario $otherUser,
        MensajeRepository $mensajeRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        /** @var Usuario $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($user === $otherUser) {
            return $this->redirectToRoute('app_chat_index');
        }

        // Marcar mensajes como leídos (opcional por ahora, pero buena práctica)
        // ...

        $messages = $mensajeRepository->findConversation($user, $otherUser);

        return $this->render('chat/show.html.twig', [
            'otherUser' => $otherUser,
            'messages' => $messages,
        ]);
    }

    #[Route('/{id}/send', name: 'app_chat_send', methods: ['POST'])]
    public function send(
        Usuario $otherUser,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var Usuario $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $content = $request->request->get('content');
        $mensaje = new Mensaje();
        $mensaje->setRemitente($user);
        $mensaje->setDestinatario($otherUser);
        $mensaje->setContenido($content);
        // Fecha envio set in constructor

        // Si se comparte una palabra (postId en request)
        $postId = $request->request->get('shared_post_id');
        if ($postId) {
            $palabra = $entityManager->getRepository(Palabra::class)->find($postId);
            if ($palabra) {
                $mensaje->setPalabraCompartida($palabra);
            }
        }

        if (empty($content) && !$mensaje->getPalabraCompartida()) {
             $this->addFlash('error', 'El mensaje no puede estar vacío.');
             return $this->redirectToRoute('app_chat_show', ['id' => $otherUser->getId()]);
        }

        $entityManager->persist($mensaje);
        $entityManager->flush();

        return $this->redirectToRoute('app_chat_show', ['id' => $otherUser->getId()]);
    }

    #[Route('/palabra/{id}/share', name: 'app_chat_share')]
    public function share(
        Palabra $palabra,
        UsuarioRepository $usuarioRepository,
        MensajeRepository $mensajeRepository
    ): Response {
        /** @var Usuario $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Obtener usuarios a los que seguimos
        // O mejor, usuarios con los que hemos hablado recientemente + seguidos
        
        $following = [];
        foreach ($user->getSeguimientosQueHace() as $seguimiento) {
             $following[] = $seguimiento->getSeguido();
        }

        // TODO: Merge with recent conversations if not in following?
        
        return $this->render('chat/share.html.twig', [
            'palabra' => $palabra,
            'users' => $following
        ]);
    }
}
