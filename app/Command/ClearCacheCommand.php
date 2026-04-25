<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:clear-cache', description: 'Delete Nette temp/cache and temp/proxies')]
final class ClearCacheCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Clearing cache');

        $tempDir = __DIR__ . '/../../temp';
        $dirs = ["{$tempDir}/cache", "{$tempDir}/proxies", "{$tempDir}/translation"];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                $io->writeln("<comment>skip</comment>  {$dir} (not found)");
                continue;
            }
            $this->removeDir($dir);
            $io->writeln("<info>removed</info> {$dir}");
        }

        $io->success('Cache cleared.');
        return Command::SUCCESS;
    }

    private function removeDir(string $dir): void
    {
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }
}
