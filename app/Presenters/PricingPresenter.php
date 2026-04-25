<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\ServiceRepository;

final class PricingPresenter extends BasePresenter
{
    public function __construct(
        private readonly ServiceRepository $services,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->pageTitle          = $this->translator->translate('pricing.page_title');
        $this->template->servicesWithPricing = $this->services->findWithPricing();
        $this->template->faqs               = $this->getFaqs();

        $this->metaDescription = $this->translator->translate('pricing.meta_desc');
    }

    /** @return list<array{question: string, answer: string}> */
    private function getFaqs(): array
    {
        return [
            [
                'question' => 'Jaká je minimální cena projektu?',
                'answer'   => 'Záleží na rozsahu projektu. Menší weby začínají od 30 000 Kč, komplexní aplikace od 100 000 Kč.',
            ],
            [
                'question' => 'Jak probíhá platba?',
                'answer'   => 'Standardně záloha 50 % na začátku a 50 % po předání. U větších projektů dohodou.',
            ],
            [
                'question' => 'Jak dlouho trvá vývoj?',
                'answer'   => 'Jednoduchý web 2–4 týdny, komplexní aplikace 2–6 měsíců. Přesný harmonogram stanovíme na discovery callu.',
            ],
            [
                'question' => 'Poskytujete maintenance po spuštění?',
                'answer'   => 'Ano, nabízím měsíční paušální správu zahrnující aktualizace, monitoring a drobné úpravy.',
            ],
            [
                'question' => 'Pracujete i na stávajících projektech?',
                'answer'   => 'Ano, mohu se připojit k existujícímu projektu jako brigádník nebo převzít jeho další rozvoj.',
            ],
        ];
    }
}
