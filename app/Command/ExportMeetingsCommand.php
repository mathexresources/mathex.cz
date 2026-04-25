<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Database\MeetingRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:export-meetings', description: 'Export meetings to CSV')]
final class ExportMeetingsCommand extends Command
{
    public function __construct(
        private readonly MeetingRepository $meetings,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('from', null, InputOption::VALUE_OPTIONAL, 'Start date (Y-m-d)')
            ->addOption('to',   null, InputOption::VALUE_OPTIONAL, 'End date (Y-m-d)')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file path (default: stdout)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $fromStr = $input->getOption('from');
        $toStr   = $input->getOption('to');
        $outFile = $input->getOption('output');

        try {
            $from = $fromStr ? new \DateTimeImmutable((string) $fromStr) : new \DateTimeImmutable('2000-01-01');
            $to   = $toStr   ? new \DateTimeImmutable((string) $toStr)   : new \DateTimeImmutable('2099-12-31');
        } catch (\Throwable $e) {
            $io->error('Invalid date format. Use Y-m-d.');
            return Command::FAILURE;
        }

        $rows = $this->meetings->findByDateRange($from, $to)->fetchAll();

        $handle = $outFile ? fopen($outFile, 'w') : fopen('php://stdout', 'w');
        if ($handle === false) {
            $io->error("Cannot open output file: {$outFile}");
            return Command::FAILURE;
        }

        fputcsv($handle, ['id', 'name', 'email', 'phone', 'company', 'service_type', 'preferred_date', 'preferred_time', 'message', 'status', 'created_at']);

        $count = 0;
        foreach ($rows as $row) {
            fputcsv($handle, [
                $row->id,
                $row->name,
                $row->email,
                $row->phone ?? '',
                $row->company ?? '',
                $row->service_type ?? '',
                $row->preferred_date ?? '',
                $row->preferred_time ? date('H:i', strtotime((string) $row->preferred_time)) : '',
                $row->message ?? '',
                $row->status,
                $row->created_at ?? '',
            ]);
            $count++;
        }

        if ($handle !== STDOUT) {
            fclose($handle);
        }

        if ($outFile) {
            $io->success(sprintf('Exported %d meetings to %s', $count, $outFile));
        } else {
            $io->writeln("<info>{$count} meetings exported.</info>", OutputInterface::VERBOSITY_VERBOSE);
        }

        return Command::SUCCESS;
    }
}
