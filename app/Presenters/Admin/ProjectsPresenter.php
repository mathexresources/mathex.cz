<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Model\Database\ProjectRepository;
use App\Model\Service\ImageUploadService;
use Nette\Application\UI\Form;
use Nette\Utils\Json;
use Nette\Utils\Strings;

final class ProjectsPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly ProjectRepository  $projects,
        private readonly ImageUploadService $imageUpload,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->pageTitle = 'Projekty';
        $this->template->projects  = $this->projects->findAllOrdered();
    }

    public function renderEdit(?int $id = null): void
    {
        $this->template->pageTitle = $id ? 'Upravit projekt' : 'Nový projekt';
        $this->template->projectId = $id;

        if ($id) {
            $project  = $this->projects->find($id) ?? $this->error('Projekt nenalezen.', 404);
            $defaults = (array) $project;

            // JSON → comma-separated for the form
            if (is_string($project->technologies)) {
                $techs = json_decode($project->technologies, true) ?? [];
                $defaults['technologies'] = implode(', ', $techs);
            }

            $this['projectForm']->setDefaults($defaults);
        }
    }

    public function actionDelete(int $id): void
    {
        $this->projects->find($id) ?? $this->error('Projekt nenalezen.', 404);
        $this->projects->delete($id);
        $this->flashMessage('Projekt byl smazán.', 'success');
        $this->redirect('default');
    }

    public function actionToggle(int $id): void
    {
        $project = $this->projects->find($id) ?? $this->error('Projekt nenalezen.', 404);
        $this->projects->update($id, ['published' => !$project->published]);
        $this->redirect('default');
    }

    public function actionToggleFeatured(int $id): void
    {
        $project = $this->projects->find($id) ?? $this->error('Projekt nenalezen.', 404);
        $this->projects->update($id, ['featured' => !$project->featured]);
        $this->redirect('default');
    }

    public function actionReorder(): void
    {
        $this->getHttpRequest()->isMethod('POST') ?: $this->error();
        $ids = $this->getRequest()->getPost('ids', []);
        if (is_array($ids)) {
            $orderedIds = [];
            foreach (array_values($ids) as $position => $id) {
                $orderedIds[(int) $id] = $position;
            }
            $this->projects->reorder($orderedIds);
        }
        $this->sendJson(['ok' => true]);
    }

    protected function createComponentProjectForm(): Form
    {
        $form = new Form();
        $form->addProtection();

        $form->addText('title', 'Název:')
            ->setRequired('Zadejte název projektu.')
            ->setMaxLength(500);

        $form->addText('slug', 'Slug (URL):')
            ->setMaxLength(255);

        $form->addTextArea('description_cs', 'Popis (CS):')
            ->setMaxLength(2000);

        $form->addTextArea('description_en', 'Popis (EN):')
            ->setMaxLength(2000);

        $form->addTextArea('case_study_content', 'Case study (Markdown):')
            ->setHtmlAttribute('rows', 12)
            ->setHtmlAttribute('class', 'form-control markdown-editor');

        $form->addText('technologies', 'Technologie (oddělené čárkou):')
            ->setMaxLength(500);

        $form->addUpload('cover_image_file', 'Cover obrázek:')
            ->addCondition(Form::FILLED)
            ->addRule(Form::IMAGE, 'Povoleny jsou JPEG, PNG, GIF a WebP.')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost je 10 MB.', 10 * 1024 * 1024);

        $form->addText('project_url', 'URL webu:')
            ->setMaxLength(500)
            ->addCondition(Form::FILLED)
            ->addRule(Form::URL, 'Zadejte platnou URL adresu.');

        $form->addText('github_url', 'GitHub URL:')
            ->setMaxLength(500)
            ->addCondition(Form::FILLED)
            ->addRule(Form::URL, 'Zadejte platnou URL adresu.');

        $form->addText('client_name', 'Klient:')
            ->setMaxLength(255);

        $form->addInteger('year', 'Rok:')
            ->setNullable();

        $form->addSelect('category', 'Kategorie:', [
            ''        => '— Vyberte —',
            'eshop'   => 'E-shop',
            'webapp'  => 'Webová aplikace',
            'api'     => 'REST API',
            'mobile'  => 'Mobilní aplikace',
            'other'   => 'Ostatní',
        ]);

        $form->addInteger('order_index', 'Pořadí:')
            ->setDefaultValue(0);

        $form->addCheckbox('featured', 'Hlavní projekt');
        $form->addCheckbox('published', 'Publikovat')->setDefaultValue(true);

        $form->addSubmit('save', 'Uložit');
        $form->onSuccess[] = [$this, 'projectFormSucceeded'];

        return $form;
    }

    public function projectFormSucceeded(Form $form, \stdClass $values): void
    {
        $id = (int) $this->getParameter('id');

        if (empty($values->slug)) {
            $values->slug = Strings::webalize($values->title);
        }

        // Comma-separated → JSON array
        $techs = array_values(array_filter(array_map('trim', explode(',', $values->technologies))));

        $data = [
            'title'              => $values->title,
            'slug'               => $values->slug,
            'description_cs'     => $values->description_cs,
            'description_en'     => $values->description_en,
            'case_study_content' => $values->case_study_content,
            'technologies'       => Json::encode($techs),
            'project_url'        => $values->project_url,
            'github_url'         => $values->github_url,
            'client_name'        => $values->client_name,
            'year'               => $values->year,
            'category'           => $values->category ?: null,
            'order_index'        => $values->order_index,
            'featured'           => $values->featured,
            'published'          => $values->published,
        ];

        if ($values->cover_image_file->isOk()) {
            try {
                $uploaded = $this->imageUpload->upload($values->cover_image_file, 'projects');
                $data['cover_image'] = $uploaded['url'];
            } catch (\RuntimeException $e) {
                $form->addError('Chyba při nahrávání obrázku: ' . $e->getMessage());
                return;
            }
        }

        if ($id) {
            $this->projects->update($id, $data);
            $this->flashMessage('Projekt byl upraven.', 'success');
        } else {
            $this->projects->insert($data);
            $this->flashMessage('Projekt byl přidán.', 'success');
        }

        $this->redirect('default');
    }
}
