<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\BlogPostRepository;
use App\Model\Database\ProjectRepository;

final class SitemapPresenter extends BasePresenter
{
    public function __construct(
        private readonly ProjectRepository  $projects,
        private readonly BlogPostRepository $blogPosts,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->getHttpResponse()->setContentType('application/xml', 'UTF-8');

        $baseUrl = rtrim(
            $this->getHttpRequest()->getUrl()->getBaseUrl(),
            '/',
        );

        $staticUrls = [
            ['loc' => $baseUrl . '/',           'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl . '/blog',        'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => $baseUrl . '/projekty',    'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl . '/sluzby',      'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl . '/ceny',        'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl . '/o-mne',       'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl . '/kontakt',     'priority' => '0.6', 'changefreq' => 'yearly'],
            ['loc' => $baseUrl . '/rezervace',   'priority' => '0.8', 'changefreq' => 'yearly'],
        ];

        $projectUrls = [];
        foreach ($this->projects->findPublished() as $project) {
            $projectUrls[] = [
                'loc'        => $baseUrl . '/projekty/' . $project->slug,
                'lastmod'    => $project->updated_at
                    ? date('Y-m-d', strtotime((string) $project->updated_at))
                    : date('Y-m-d'),
                'priority'   => '0.8',
                'changefreq' => 'monthly',
            ];
        }

        $postUrls = [];
        foreach ($this->blogPosts->findPublished() as $post) {
            $postUrls[] = [
                'loc'        => $baseUrl . '/blog/' . $post->slug,
                'lastmod'    => $post->updated_at
                    ? date('Y-m-d', strtotime((string) $post->updated_at))
                    : date('Y-m-d'),
                'priority'   => '0.7',
                'changefreq' => 'monthly',
            ];
        }

        $this->template->urls = array_merge($staticUrls, $projectUrls, $postUrls);
        $this->setLayout(false);
    }
}
