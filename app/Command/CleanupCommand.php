<?php

declare(strict_types=1);

namespace App\Command;

use Nette\Database\Explorer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:cleanup', description: 'Delete old contact messages (>2y) and page_views (>90d)')]
final class CleanupCommand extends Command
{
    public function __construct(
        private readonly Explorer $db,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('GDPR data retention cleanup');

        $deleted = $this->db->query(
            'DELETE FROM contact_messages WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR)',
        )->getRowCount();
        $io->writeln("contact_messages deleted: <info>{$deleted}</info>");

        $deleted = $this->db->query(
            'DELETE FROM page_views WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)',
        )->getRowCount();
        $io->writeln("page_views deleted:       <info>{$deleted}</info>");

        $io->success('Cleanup complete.');
        return Command::SUCCESS;
    }
}
