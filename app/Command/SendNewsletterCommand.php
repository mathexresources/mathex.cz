<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Database\NewsletterRepository;
use App\Model\Service\NewsletterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:send-newsletter', description: 'Send a newsletter to all confirmed subscribers')]
final class SendNewsletterCommand extends Command
{
    public function __construct(
        private readonly NewsletterService    $newsletterService,
        private readonly NewsletterRepository $newsletter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('subject', InputArgument::REQUIRED, 'E-mail subject')
            ->addOption('body-file', 'f', InputOption::VALUE_OPTIONAL, 'Path to HTML body file (default: read from stdin)')
            ->addOption('dry-run',   null, InputOption::VALUE_NONE,     'Show recipient count without sending');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $subject  = (string) $input->getArgument('subject');
        $bodyFile = $input->getOption('body-file');
        $dryRun   = (bool) $input->getOption('dry-run');

        $count = $this->newsletter->getConfirmedCount();
        $io->writeln("Confirmed subscribers: <info>{$count}</info>");

        if ($dryRun) {
            $io->note('Dry run — no e-mails sent.');
            return Command::SUCCESS;
        }

        if ($count === 0) {
            $io->warning('No confirmed subscribers. Nothing to send.');
            return Command::SUCCESS;
        }

        if ($bodyFile !== null) {
            if (!is_readable((string) $bodyFile)) {
                $io->error("Cannot read file: {$bodyFile}");
                return Command::FAILURE;
            }
            $htmlBody = file_get_contents((string) $bodyFile);
        } else {
            $io->writeln('Reading HTML body from stdin (Ctrl+D to finish):');
            $htmlBody = stream_get_contents(STDIN);
        }

        if ($htmlBody === false || trim($htmlBody) === '') {
            $io->error('Empty body — newsletter not sent.');
            return Command::FAILURE;
        }

        if (!$io->confirm("Send newsletter \"{$subject}\" to {$count} subscribers?", false)) {
            $io->note('Cancelled.');
            return Command::SUCCESS;
        }

        $sent = $this->newsletterService->broadcast($subject, $htmlBody);
        $io->success("Newsletter sent to {$sent} subscribers.");
        return Command::SUCCESS;
    }
}
