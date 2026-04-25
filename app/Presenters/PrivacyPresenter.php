<?php

declare(strict_types=1);

namespace App\Presenters;

final class PrivacyPresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $this->template->pageTitle = $this->translator->translate('privacy.page_title');
        $this->metaDescription     = $this->translator->translate('privacy.meta_desc');
        $this->canonicalUrl        = $this->link('//Privacy:default');
    }
}
