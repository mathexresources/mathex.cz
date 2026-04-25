<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\ServiceRepository;

final class ServicesPresenter extends BasePresenter
{
    public function __construct(
        private readonly ServiceRepository $services,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->pageTitle       = $this->translator->translate('services.page_title');
        $this->template->services        = $this->services->findActive();
        $this->template->servicesWithPricing = $this->services->findWithPricing();

        $this->metaDescription = $this->translator->translate('services.meta_desc');
        $this->jsonLd = [
            '@context' => 'https://schema.org',
            '@type'    => 'ItemList',
            'name'     => 'Programátorské služby',
            'url'      => $this->link('//Services:default'),
        ];
    }
}
