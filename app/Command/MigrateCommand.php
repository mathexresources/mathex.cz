<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:migrate', description: 'Run pending Phinx migrations')]
final class MigrateCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Running migrations');

        $phinxBin = __DIR__ . '/../../vendor/bin/phinx';
        $configFile = __DIR__ . '/../../phinx.php';

        passthru(sprintf(
            '%s migrate -e development -c %s',
            escapeshellarg($phinxBin),
            escapeshellarg($configFile),
        ), $exitCode);

        if ($exitCode !== 0) {
            $io->error('Migration failed.');
            return Command::FAILURE;
        }

        $io->success('Migrations complete.');
        return Command::SUCCESS;
    }
}
