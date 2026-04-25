<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\BlogPostRepository;
use App\Model\Database\ProjectRepository;
use App\Model\Database\ServiceRepository;
use App\Model\Database\SkillRepository;
use App\Model\Database\TestimonialRepository;

final class HomepagePresenter extends BasePresenter
{
    public function __construct(
        private readonly ProjectRepository     $projects,
        private readonly BlogPostRepository    $blogPosts,
        private readonly TestimonialRepository $testimonials,
        private readonly SkillRepository       $skills,
        private readonly ServiceRepository     $services,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->pageTitle        = $this->translator->translate('homepage.page_title');
        $this->template->featuredProjects = $this->projects->findFeatured(3);
        $this->template->featuredPosts    = $this->blogPosts->findFeatured(3);
        $this->template->testimonials     = $this->testimonials->findFeatured(6);
        $this->template->skillsGrouped    = $this->skills->findAllGrouped();
        $this->template->services         = $this->services->findActive();
        $this->template->stats            = [
            'years'        => 5,
            'projects'     => 50,
            'clients'      => 30,
            'satisfaction' => 98,
        ];

        $this->metaDescription = $this->translator->translate('homepage.meta_desc');
        $this->jsonLd = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'       => 'WebSite',
                    '@id'         => 'https://mathex.cz/#website',
                    'url'         => 'https://mathex.cz',
                    'name'        => 'Mathex.cz',
                    'description' => $this->metaDescription,
                    'inLanguage'  => ['cs', 'en'],
                ],
                [
                    '@type'       => 'Person',
                    '@id'         => 'https://mathex.cz/#person',
                    'name'        => 'Mathex',
                    'url'         => 'https://mathex.cz',
                    'jobTitle'    => 'Freelance PHP Developer',
                    'description' => $this->metaDescription,
                    'sameAs'      => [
                        'https://github.com/mathex',
                        'https://linkedin.com/in/mathex',
                    ],
                ],
            ],
        ];
    }
}
