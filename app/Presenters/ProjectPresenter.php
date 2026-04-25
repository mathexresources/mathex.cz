<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\ProjectRepository;
use Nette\Application\BadRequestException;

final class ProjectPresenter extends BasePresenter
{
    public function __construct(
        private readonly ProjectRepository $projects,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->projects  = $this->projects->findPublished();
        $this->template->pageTitle = 'Portfolio & projekty';
    }

    public function renderDetail(string $slug): void
    {
        $project = $this->projects->findBySlug($slug);

        if (!$project || !$project->published) {
            throw new BadRequestException('Projekt nenalezen.', 404);
        }

        $this->template->project   = $project;
        $this->template->pageTitle = $project->title;
    }
}
