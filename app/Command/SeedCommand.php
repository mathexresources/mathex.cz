<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:seed', description: 'Seed sample data via Phinx')]
final class SeedCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('seeder', 's', InputOption::VALUE_OPTIONAL, 'Specific seeder class to run', 'MainSeeder');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $seeder = (string) $input->getOption('seeder');
        $io->title("Running seeder: {$seeder}");

        $phinxBin = __DIR__ . '/../../vendor/bin/phinx';
        $configFile = __DIR__ . '/../../phinx.php';

        passthru(sprintf(
            '%s seed:run -s %s -e development -c %s',
            escapeshellarg($phinxBin),
            escapeshellarg($seeder),
            escapeshellarg($configFile),
        ), $exitCode);

        if ($exitCode !== 0) {
            $io->error('Seeding failed.');
            return Command::FAILURE;
        }

        $io->success('Seeding complete.');
        return Command::SUCCESS;
    }
}
