<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Database\UserRepository;
use Nette\Security\Passwords;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:create-admin', description: 'Create or update an admin user')]
final class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly Passwords $passwords,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Admin e-mail address')
            ->addArgument('password', InputArgument::REQUIRED, 'Admin password (min 8 chars)')
            ->addArgument('name', InputArgument::OPTIONAL, 'Display name', 'Admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email    = (string) $input->getArgument('email');
        $password = (string) $input->getArgument('password');
        $name     = (string) $input->getArgument('name');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Invalid e-mail address.');
            return Command::FAILURE;
        }

        if (strlen($password) < 8) {
            $io->error('Password must be at least 8 characters.');
            return Command::FAILURE;
        }

        $hash     = $this->passwords->hash($password);
        $existing = $this->users->findByEmail($email);

        if ($existing) {
            $this->users->update((int) $existing->id, [
                'name'          => $name,
                'password_hash' => $hash,
                'role'          => 'admin',
                'is_active'     => true,
            ]);
            $io->success("Admin user updated: {$email}");
        } else {
            $this->users->insert([
                'email'         => $email,
                'name'          => $name,
                'password_hash' => $hash,
                'role'          => 'admin',
                'is_active'     => true,
            ]);
            $io->success("Admin user created: {$email}");
        }

        return Command::SUCCESS;
    }
}
