<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Model\Database\ProjectRepository;
use App\Model\Database\TestimonialRepository;
use App\Model\Service\ImageUploadService;
use Nette\Application\UI\Form;

final class TestimonialsPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly TestimonialRepository $testimonials,
        private readonly ProjectRepository     $projects,
        private readonly ImageUploadService    $imageUpload,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->pageTitle   = 'Posudky';
        $this->template->testimonials = $this->testimonials->findAll()->order('order_index ASC');
    }

    public function renderEdit(?int $id = null): void
    {
        $this->template->pageTitle     = $id ? 'Upravit posudek' : 'Nový posudek';
        $this->template->testimonialId = $id;

        if ($id) {
            $t = $this->testimonials->find($id) ?? $this->error('Posudek nenalezen.', 404);
            $this['testimonialForm']->setDefaults((array) $t);
        }
    }

    public function actionDelete(int $id): void
    {
        $this->testimonials->find($id) ?? $this->error('Posudek nenalezen.', 404);
        $this->testimonials->delete($id);
        $this->flashMessage('Posudek byl smazán.', 'success');
        $this->redirect('default');
    }

    public function actionToggle(int $id): void
    {
        $t = $this->testimonials->find($id) ?? $this->error('Posudek nenalezen.', 404);
        $this->testimonials->update($id, ['active' => !$t->active]);
        $this->redirect('default');
    }

    public function actionToggleFeatured(int $id): void
    {
        $t = $this->testimonials->find($id) ?? $this->error('Posudek nenalezen.', 404);
        $this->testimonials->update($id, ['featured' => !$t->featured]);
        $this->redirect('default');
    }

    public function actionReorder(): void
    {
        $ids = $this->getRequest()->getPost('ids', []);
        if (is_array($ids)) {
            foreach (array_values($ids) as $position => $id) {
                $this->testimonials->update((int) $id, ['order_index' => $position]);
            }
        }
        $this->sendJson(['ok' => true]);
    }

    protected function createComponentTestimonialForm(): Form
    {
        $form = new Form();
        $form->addProtection();

        $form->addText('client_name', 'Jméno klienta:')
            ->setRequired('Zadejte jméno.')
            ->setMaxLength(255);

        $form->addText('client_company', 'Firma:')
            ->setMaxLength(255);

        $form->addText('client_position', 'Pozice:')
            ->setMaxLength(255);

        $form->addUpload('avatar_file', 'Fotografie klienta:')
            ->addCondition(Form::FILLED)
            ->addRule(Form::IMAGE, 'Povoleny jsou JPEG, PNG, GIF a WebP.')
            ->addRule(Form::MAX_FILE_SIZE, 'Max 10 MB.', 10 * 1024 * 1024);

        $form->addTextArea('text_cs', 'Text posudku (CS):')
            ->setRequired('Zadejte text posudku.')
            ->setHtmlAttribute('rows', 5);

        $form->addTextArea('text_en', 'Text posudku (EN):')
            ->setHtmlAttribute('rows', 5);

        $form->addSelect('rating', 'Hodnocení (hvězdy):', array_combine(range(1, 5), range(1, 5)))
            ->setDefaultValue(5);

        $projectOptions = ['' => '— Žádný projekt —'];
        foreach ($this->projects->findAll()->order('title ASC') as $p) {
            $projectOptions[$p->id] = $p->title;
        }
        $form->addSelect('project_id', 'Projekt:', $projectOptions);

        $form->addInteger('order_index', 'Pořadí:')
            ->setDefaultValue(0);

        $form->addCheckbox('featured', 'Hlavní posudek');
        $form->addCheckbox('active', 'Aktivní')->setDefaultValue(true);

        $form->addSubmit('save', 'Uložit');
        $form->onSuccess[] = [$this, 'testimonialFormSucceeded'];

        return $form;
    }

    public function testimonialFormSucceeded(Form $form, \stdClass $values): void
    {
        $id = (int) $this->getParameter('id');

        $data = [
            'client_name'     => $values->client_name,
            'client_company'  => $values->client_company,
            'client_position' => $values->client_position,
            'text_cs'         => $values->text_cs,
            'text_en'         => $values->text_en,
            'rating'          => $values->rating,
            'project_id'      => $values->project_id ?: null,
            'order_index'     => $values->order_index,
            'featured'        => $values->featured,
            'active'          => $values->active,
        ];

        if ($values->avatar_file->isOk()) {
            try {
                $uploaded = $this->imageUpload->upload($values->avatar_file, 'testimonials', 200, 200);
                $data['avatar_url'] = $uploaded['url'];
            } catch (\RuntimeException $e) {
                $form->addError('Chyba při nahrávání fotografie: ' . $e->getMessage());
                return;
            }
        }

        if ($id) {
            $this->testimonials->update($id, $data);
            $this->flashMessage('Posudek byl uložen.', 'success');
        } else {
            $this->testimonials->insert($data);
            $this->flashMessage('Posudek byl přidán.', 'success');
        }

        $this->redirect('default');
    }
}
