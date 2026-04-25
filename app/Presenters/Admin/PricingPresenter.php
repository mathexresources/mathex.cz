<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Model\Database\PricingRepository;
use App\Model\Database\ServiceRepository;
use Nette\Application\UI\Form;
use Nette\Utils\Json;

final class PricingPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly PricingRepository $pricing,
        private readonly ServiceRepository $services,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->pageTitle = 'Ceník';
        $this->template->plans    = $this->pricing->findAllOrdered();
        $this->template->services = $this->services->findAll()->order('order_index ASC');
    }

    public function renderEdit(?int $id = null): void
    {
        $this->template->pageTitle = $id ? 'Upravit cenový plán' : 'Nový cenový plán';
        $this->template->planId    = $id;
        $this->template->services  = $this->services->findAll()->order('order_index ASC');

        if ($id) {
            $plan     = $this->pricing->find($id) ?? $this->error('Plán nenalezen.', 404);
            $defaults = (array) $plan;
            if (is_string($plan->features_cs)) {
                $defaults['features_cs'] = implode("\n", json_decode($plan->features_cs, true) ?? []);
            }
            if (is_string($plan->features_en)) {
                $defaults['features_en'] = implode("\n", json_decode($plan->features_en, true) ?? []);
            }
            $this['planForm']->setDefaults($defaults);
        }
    }

    public function actionDelete(int $id): void
    {
        $this->pricing->find($id) ?? $this->error('Plán nenalezen.', 404);
        $this->pricing->delete($id);
        $this->flashMessage('Cenový plán byl smazán.', 'success');
        $this->redirect('default');
    }

    public function actionToggle(int $id): void
    {
        $plan = $this->pricing->find($id) ?? $this->error('Plán nenalezen.', 404);
        $this->pricing->update($id, ['active' => !$plan->active]);
        $this->redirect('default');
    }

    protected function createComponentPlanForm(): Form
    {
        $form = new Form();
        $form->addProtection();

        $serviceOptions = ['' => '— Bez služby —'];
        foreach ($this->services->findAll()->order('order_index ASC') as $s) {
            $serviceOptions[$s->id] = $s->title_cs;
        }

        $form->addSelect('service_id', 'Služba:', $serviceOptions)
            ->setPrompt(false);

        $form->addText('name_cs', 'Název plánu (CS):')
            ->setRequired('Zadejte název.')
            ->setMaxLength(255);

        $form->addText('name_en', 'Název plánu (EN):')
            ->setMaxLength(255);

        $form->addText('price_from', 'Cena od (Kč):')
            ->setHtmlType('number')
            ->setNullable();

        $form->addText('price_to', 'Cena do (Kč):')
            ->setHtmlType('number')
            ->setNullable();

        $form->addText('currency', 'Měna:')
            ->setDefaultValue('CZK')
            ->setMaxLength(10);

        $form->addSelect('billing_type', 'Typ fakturace:', [
            'fixed'   => 'Fixní cena',
            'hourly'  => 'Hodinová sazba',
            'monthly' => 'Měsíční paušál',
        ]);

        $form->addTextArea('features_cs', 'Co zahrnuje (CS, jeden bod na řádek):')
            ->setHtmlAttribute('rows', 6);

        $form->addTextArea('features_en', 'Co zahrnuje (EN, jeden bod na řádek):')
            ->setHtmlAttribute('rows', 6);

        $form->addInteger('order_index', 'Pořadí:')
            ->setDefaultValue(0);

        $form->addCheckbox('highlighted', 'Doporučený (zvýraznit)');
        $form->addCheckbox('active', 'Aktivní')->setDefaultValue(true);

        $form->addSubmit('save', 'Uložit');
        $form->onSuccess[] = [$this, 'planFormSucceeded'];

        return $form;
    }

    public function planFormSucceeded(Form $form, \stdClass $values): void
    {
        $id = (int) $this->getParameter('id');

        $parseFeatures = static function (string $text): string {
            $items = array_values(array_filter(array_map('trim', explode("\n", $text))));
            return Json::encode($items);
        };

        $data = [
            'service_id'   => $values->service_id ?: null,
            'name_cs'      => $values->name_cs,
            'name_en'      => $values->name_en,
            'price_from'   => $values->price_from,
            'price_to'     => $values->price_to,
            'currency'     => $values->currency,
            'billing_type' => $values->billing_type,
            'features_cs'  => $parseFeatures($values->features_cs),
            'features_en'  => $parseFeatures($values->features_en),
            'order_index'  => $values->order_index,
            'highlighted'  => $values->highlighted,
            'active'       => $values->active,
        ];

        if ($id) {
            $this->pricing->update($id, $data);
            $this->flashMessage('Cenový plán byl uložen.', 'success');
        } else {
            $this->pricing->insert($data);
            $this->flashMessage('Cenový plán byl přidán.', 'success');
        }

        $this->redirect('default');
    }
}
