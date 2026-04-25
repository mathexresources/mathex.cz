<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Database\BlogPostRepository;
use App\Model\Database\ProjectRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:generate-sitemap', description: 'Regenerate www/sitemap.xml')]
final class GenerateSitemapCommand extends Command
{
    public function __construct(
        private readonly BlogPostRepository $blogPosts,
        private readonly ProjectRepository  $projects,
        private readonly string $siteUrl,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Generating sitemap.xml');

        $base = rtrim($this->siteUrl, '/');
        $today = date('Y-m-d');

        $urls = [
            ['loc' => $base . '/',         'priority' => '1.0', 'changefreq' => 'weekly',  'lastmod' => $today],
            ['loc' => $base . '/blog',     'priority' => '0.9', 'changefreq' => 'daily',   'lastmod' => $today],
            ['loc' => $base . '/projekty', 'priority' => '0.9', 'changefreq' => 'weekly',  'lastmod' => $today],
            ['loc' => $base . '/sluzby',   'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $today],
            ['loc' => $base . '/ceny',     'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $today],
            ['loc' => $base . '/o-mne',    'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $today],
            ['loc' => $base . '/kontakt',  'priority' => '0.6', 'changefreq' => 'yearly',  'lastmod' => $today],
            ['loc' => $base . '/rezervace','priority' => '0.8', 'changefreq' => 'yearly',  'lastmod' => $today],
            ['loc' => $base . '/soukromi', 'priority' => '0.3', 'changefreq' => 'yearly',  'lastmod' => $today],
            // English alternates
            ['loc' => $base . '/en',              'priority' => '0.9', 'changefreq' => 'weekly',  'lastmod' => $today],
            ['loc' => $base . '/en/blog',         'priority' => '0.8', 'changefreq' => 'daily',   'lastmod' => $today],
            ['loc' => $base . '/en/projects',     'priority' => '0.8', 'changefreq' => 'weekly',  'lastmod' => $today],
            ['loc' => $base . '/en/services',     'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $today],
            ['loc' => $base . '/en/pricing',      'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $today],
            ['loc' => $base . '/en/about',        'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $today],
            ['loc' => $base . '/en/contact',      'priority' => '0.5', 'changefreq' => 'yearly',  'lastmod' => $today],
            ['loc' => $base . '/en/privacy',      'priority' => '0.3', 'changefreq' => 'yearly',  'lastmod' => $today],
        ];

        foreach ($this->projects->findPublished() as $project) {
            $lastmod = $project->updated_at
                ? date('Y-m-d', strtotime((string) $project->updated_at))
                : $today;
            $urls[] = ['loc' => $base . '/projekty/' . $project->slug, 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $lastmod];
        }

        foreach ($this->blogPosts->findPublished() as $post) {
            $lastmod = $post->updated_at
                ? date('Y-m-d', strtotime((string) $post->updated_at))
                : $today;
            $urls[] = ['loc' => $base . '/blog/' . $post->slug, 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $lastmod];
        }

        $xml = $this->buildXml($urls);
        $dest = __DIR__ . '/../../www/sitemap.xml';
        file_put_contents($dest, $xml);

        $io->success(sprintf('sitemap.xml written (%d URLs).', count($urls)));
        return Command::SUCCESS;
    }

    /** @param list<array<string, string>> $urls */
    private function buildXml(array $urls): string
    {
        $lines = ['<?xml version="1.0" encoding="UTF-8"?>'];
        $lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>' . htmlspecialchars($url['loc']) . '</loc>';
            if (isset($url['lastmod'])) {
                $lines[] = '    <lastmod>' . $url['lastmod'] . '</lastmod>';
            }
            if (isset($url['changefreq'])) {
                $lines[] = '    <changefreq>' . $url['changefreq'] . '</changefreq>';
            }
            if (isset($url['priority'])) {
                $lines[] = '    <priority>' . $url['priority'] . '</priority>';
            }
            $lines[] = '  </url>';
        }
        $lines[] = '</urlset>';
        return implode("\n", $lines) . "\n";
    }
}
