<?php

namespace App\Command;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Promotes a user to admin or creates a new one.',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private UsuarioRepository $usuarioRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user')
            ->addArgument('password', InputArgument::OPTIONAL, 'The password (only if creating new user)')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name (only if creating new user)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $name = $input->getArgument('name');

        $user = $this->usuarioRepository->findOneBy(['email' => $email]);

        if ($user) {
            $roles = $user->getRoles();
            if (!in_array('ROLE_ADMIN', $roles)) {
                $roles[] = 'ROLE_ADMIN';
                $user->setRoles($roles);
                $this->entityManager->flush();
                $io->success(sprintf('User %s has been promoted to admin.', $email));
            } else {
                $io->note(sprintf('User %s is already an admin.', $email));
            }
        } else {
            if (!$password || !$name) {
                $io->error('User not found. To create a new user, you must provide password and name arguments.');
                return Command::FAILURE;
            }

            $user = new Usuario();
            $user->setEmail($email);
            $user->setNombre($name);
            $user->setRoles(['ROLE_ADMIN']);
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $password
                )
            );
            $today = new \DateTime('now');
            $user->setFechaRegistro($today);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $io->success(sprintf('User %s has been created as an admin.', $email));
        }

        return Command::SUCCESS;
    }
}
