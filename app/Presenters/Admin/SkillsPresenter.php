<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Model\Database\SkillRepository;
use Nette\Application\UI\Form;

final class SkillsPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly SkillRepository $skills,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->pageTitle = 'Dovednosti';
        $this->template->grouped   = $this->skills->findAllGrouped();
    }

    public function renderEdit(?int $id = null): void
    {
        $this->template->pageTitle = $id ? 'Upravit dovednost' : 'Nová dovednost';
        $this->template->skillId   = $id;

        if ($id) {
            $skill = $this->skills->find($id) ?? $this->error('Dovednost nenalezena.', 404);
            $this['skillForm']->setDefaults((array) $skill);
        }
    }

    public function actionDelete(int $id): void
    {
        $this->skills->find($id) ?? $this->error('Dovednost nenalezena.', 404);
        $this->skills->delete($id);
        $this->flashMessage('Dovednost byla smazána.', 'success');
        $this->redirect('default');
    }

    public function actionReorder(): void
    {
        $ids = $this->getRequest()->getPost('ids', []);
        if (is_array($ids)) {
            foreach (array_values($ids) as $position => $id) {
                $this->skills->update((int) $id, ['order_index' => $position]);
            }
        }
        $this->sendJson(['ok' => true]);
    }

    protected function createComponentSkillForm(): Form
    {
        $form = new Form();
        $form->addProtection();

        $form->addText('name', 'Název dovednosti:')
            ->setRequired('Zadejte název.')
            ->setMaxLength(100);

        $form->addSelect('category', 'Kategorie:', [
            'backend'  => 'Backend',
            'frontend' => 'Frontend',
            'devops'   => 'DevOps',
            'other'    => 'Ostatní',
        ]);

        $form->addText('icon_class', 'CSS třída ikony (devicon):')
            ->setMaxLength(100)
            ->setHtmlAttribute('placeholder', 'devicon-php-plain');

        $form->addInteger('level', 'Úroveň (1–100):')
            ->setRequired('Zadejte úroveň.')
            ->setDefaultValue(80)
            ->addRule(Form::RANGE, 'Hodnota musí být 1–100.', [1, 100])
            ->setHtmlAttribute('type', 'range')
            ->setHtmlAttribute('min', 1)
            ->setHtmlAttribute('max', 100)
            ->setHtmlAttribute('class', 'skill-slider');

        $form->addInteger('order_index', 'Pořadí:')
            ->setDefaultValue(0);

        $form->addSubmit('save', 'Uložit');
        $form->onSuccess[] = [$this, 'skillFormSucceeded'];

        return $form;
    }

    public function skillFormSucceeded(Form $form, \stdClass $values): void
    {
        $id = (int) $this->getParameter('id');

        $data = [
            'name'        => $values->name,
            'category'    => $values->category,
            'icon_class'  => $values->icon_class,
            'level'       => $values->level,
            'order_index' => $values->order_index,
        ];

        if ($id) {
            $this->skills->update($id, $data);
            $this->flashMessage('Dovednost byla uložena.', 'success');
        } else {
            $this->skills->insert($data);
            $this->flashMessage('Dovednost byla přidána.', 'success');
        }

        $this->redirect('default');
    }
}
