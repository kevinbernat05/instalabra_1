<?php

namespace App\Repository;

use App\Entity\Mensaje;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mensaje>
 *
 * @method Mensaje|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mensaje|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mensaje[]    findAll()
 * @method Mensaje[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MensajeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mensaje::class);
    }

    /**
     * Encuentra los últimos mensajes de cada conversación para el usuario dado.
     * Esto es complejo en Doctrine, a veces es mejor traer todos y filtrar en PHP o usar una query nativa.
     * Para MVP, vamos a obtener todos los usuarios con los que el usuario ha hablado.
     */
    public function findRecentConversations(Usuario $user): array
    {
        // Esta query busca mensajes donde el usuario es remitente o destinatario
        // y agrupa por el "otro" usuario para tener una lista de chats únicos.
        
        $qb = $this->createQueryBuilder('m');
        
        // Obtenemos todos los mensajes que involucran al usuario
        $mensajes = $qb->join('m.remitente', 'r')
            ->join('m.destinatario', 'd')
            ->where('(m.remitente = :user OR m.destinatario = :user)')
            ->andWhere('r.isBlocked = :blocked')
            ->andWhere('d.isBlocked = :blocked')
            ->setParameter('user', $user)
            ->setParameter('blocked', false)
            ->orderBy('m.fechaEnvio', 'DESC')
            ->getQuery()
            ->getResult();

        $conversations = [];
        $seenUsers = [];

        foreach ($mensajes as $mensaje) {
            $otherUser = $mensaje->getRemitente() === $user ? $mensaje->getDestinatario() : $mensaje->getRemitente();
            
            if (!in_array($otherUser->getId(), $seenUsers)) {
                $seenUsers[] = $otherUser->getId();
                $conversations[] = [
                    'user' => $otherUser,
                    'lastMessage' => $mensaje
                ];
            }
        }

        return $conversations;
    }

    public function findConversation(Usuario $user1, Usuario $user2): array
    {
        return $this->createQueryBuilder('m')
            ->join('m.remitente', 'r')
            ->where('(m.remitente = :user1 AND m.destinatario = :user2)')
            ->orWhere('(m.remitente = :user2 AND m.destinatario = :user1)')
            ->andWhere('r.isBlocked = :blocked')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->setParameter('blocked', false)
            ->orderBy('m.fechaEnvio', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
