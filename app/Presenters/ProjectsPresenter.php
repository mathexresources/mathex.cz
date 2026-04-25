<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\ProjectRepository;
use App\Model\Database\TestimonialRepository;
use App\Model\Service\MarkdownService;
use Nette\Application\BadRequestException;

final class ProjectsPresenter extends BasePresenter
{
    public function __construct(
        private readonly ProjectRepository     $projects,
        private readonly TestimonialRepository $testimonials,
        private readonly MarkdownService       $markdown,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $category = (string) ($this->getParameter('category') ?? '');

        $projects = $category !== ''
            ? $this->projects->findByCategory($category)
            : $this->projects->findPublished();

        $categories = $this->projects->findDistinctCategories();

        $this->template->pageTitle  = $this->translator->translate('projects.page_title');
        $this->template->projects   = $projects;
        $this->template->categories = array_filter($categories);
        $this->template->activeCategory = $category;

        $this->metaDescription = $this->translator->translate('projects.meta_desc');
    }

    public function renderDetail(string $slug): void
    {
        $project = $this->projects->findBySlug($slug);

        if (!$project || !$project->published) {
            throw new BadRequestException('Projekt nenalezen.', 404);
        }

        $gallery      = $project->gallery_images ? json_decode((string) $project->gallery_images, true) : [];
        $technologies = $project->technologies   ? json_decode((string) $project->technologies, true)   : [];
        $caseStudyHtml = $project->case_study_content
            ? $this->markdown->toHtml((string) $project->case_study_content)
            : null;

        $testimonials = $this->testimonials->findByProject($project->id);

        $this->template->pageTitle    = (string) $project->title;
        $this->template->project      = $project;
        $this->template->gallery      = $gallery;
        $this->template->technologies = $technologies;
        $this->template->caseStudy    = $caseStudyHtml;
        $this->template->testimonials = $testimonials;

        $desc = (string) ($project->{'description_' . $this->locale} ?? $project->description_cs ?? $project->description_en ?? '');
        $this->metaDescription = $desc !== '' ? $desc : $this->metaDescription;
        $this->ogImage         = $project->cover_image ?? null;
        $this->canonicalUrl    = $this->link('//Projects:detail', $slug);
        $this->jsonLd = [
            '@context'    => 'https://schema.org',
            '@type'       => 'SoftwareApplication',
            'name'        => $project->title,
            'description' => $desc,
            'url'         => $project->project_url,
            'author'      => ['@type' => 'Person', 'name' => 'Mathex'],
        ];
    }
}
