<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Model\Database\ServiceRepository;
use Nette\Application\UI\Form;
use Nette\Utils\Json;
use Nette\Utils\Strings;

final class ServicesPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly ServiceRepository $services,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->pageTitle = 'Služby';
        $this->template->services  = $this->services->findAll()->order('order_index ASC');
    }

    public function renderEdit(?int $id = null): void
    {
        $this->template->pageTitle = $id ? 'Upravit službu' : 'Nová služba';
        $this->template->serviceId = $id;

        if ($id) {
            $service  = $this->services->find($id) ?? $this->error('Služba nenalezena.', 404);
            $defaults = (array) $service;
            if (is_string($service->features_cs)) {
                $defaults['features_cs'] = implode("\n", json_decode($service->features_cs, true) ?? []);
            }
            if (is_string($service->features_en)) {
                $defaults['features_en'] = implode("\n", json_decode($service->features_en, true) ?? []);
            }
            $this['serviceForm']->setDefaults($defaults);
        }
    }

    public function actionDelete(int $id): void
    {
        $this->services->find($id) ?? $this->error('Služba nenalezena.', 404);
        $this->services->delete($id);
        $this->flashMessage('Služba byla smazána.', 'success');
        $this->redirect('default');
    }

    public function actionToggle(int $id): void
    {
        $service = $this->services->find($id) ?? $this->error('Služba nenalezena.', 404);
        $this->services->update($id, ['active' => !$service->active]);
        $this->redirect('default');
    }

    protected function createComponentServiceForm(): Form
    {
        $form = new Form();
        $form->addProtection();

        $form->addText('title_cs', 'Název (CS):')
            ->setRequired('Zadejte název.')
            ->setMaxLength(255);

        $form->addText('title_en', 'Název (EN):')
            ->setMaxLength(255);

        $form->addText('slug', 'Slug (URL):')
            ->setMaxLength(255);

        $form->addText('icon', 'Ikona (CSS třída/emoji):')
            ->setMaxLength(100);

        $form->addTextArea('description_cs', 'Popis (CS):')
            ->setHtmlAttribute('rows', 4);

        $form->addTextArea('description_en', 'Popis (EN):')
            ->setHtmlAttribute('rows', 4);

        $form->addTextArea('features_cs', 'Funkce (CS, jeden bod na řádek):')
            ->setHtmlAttribute('rows', 6)
            ->setHtmlAttribute('placeholder', 'Responzivní design' . "\n" . 'SEO optimalizace');

        $form->addTextArea('features_en', 'Funkce (EN, jeden bod na řádek):')
            ->setHtmlAttribute('rows', 6);

        $form->addInteger('order_index', 'Pořadí:')
            ->setDefaultValue(0);

        $form->addCheckbox('active', 'Aktivní')->setDefaultValue(true);

        $form->addSubmit('save', 'Uložit');
        $form->onSuccess[] = [$this, 'serviceFormSucceeded'];

        return $form;
    }

    public function serviceFormSucceeded(Form $form, \stdClass $values): void
    {
        $id = (int) $this->getParameter('id');

        if (empty($values->slug)) {
            $values->slug = Strings::webalize($values->title_cs);
        }

        $parseFeatures = static function (string $text): string {
            $items = array_values(array_filter(array_map('trim', explode("\n", $text))));
            return Json::encode($items);
        };

        $data = [
            'title_cs'       => $values->title_cs,
            'title_en'       => $values->title_en,
            'slug'           => $values->slug,
            'icon'           => $values->icon,
            'description_cs' => $values->description_cs,
            'description_en' => $values->description_en,
            'features_cs'    => $parseFeatures($values->features_cs),
            'features_en'    => $parseFeatures($values->features_en),
            'order_index'    => $values->order_index,
            'active'         => $values->active,
        ];

        if ($id) {
            $this->services->update($id, $data);
            $this->flashMessage('Služba byla uložena.', 'success');
        } else {
            $this->services->insert($data);
            $this->flashMessage('Služba byla přidána.', 'success');
        }

        $this->redirect('default');
    }
}
