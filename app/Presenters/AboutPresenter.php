<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\SkillRepository;

final class AboutPresenter extends BasePresenter
{
    public function __construct(
        private readonly SkillRepository $skills,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->pageTitle    = $this->translator->translate('about.page_title');
        $this->template->skillsGrouped = $this->skills->findAllGrouped();
        $this->template->timeline     = $this->getTimeline();
        $this->template->values       = $this->getValues();
        $this->template->cvUrl        = '/uploads/cv-mathex.pdf';

        $this->metaDescription = $this->translator->translate('about.meta_desc');
        $this->jsonLd = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Person',
            'name'        => 'Mathex',
            'url'         => 'https://mathex.cz',
            'jobTitle'    => 'Freelance PHP Developer',
            'description' => $this->metaDescription,
            'knowsAbout'  => ['PHP', 'Nette', 'MySQL', 'Vue.js', 'REST API'],
        ];
    }

    /** @return list<array{year: string, title: string, description: string}> */
    private function getTimeline(): array
    {
        return [
            [
                'year'        => '2024–nyní',
                'title'       => 'Senior Freelance PHP Developer',
                'description' => 'Vývoj komplexních webových aplikací a e-shopů pro české a slovenské klienty.',
            ],
            [
                'year'        => '2021–2024',
                'title'       => 'PHP Developer – agentura',
                'description' => 'Vedení technického vývoje, architektura REST API, mentoring juniorů.',
            ],
            [
                'year'        => '2019–2021',
                'title'       => 'Junior PHP Developer',
                'description' => 'Vývoj webů a e-shopů v Nette Framework a WordPress.',
            ],
            [
                'year'        => '2019',
                'title'       => 'Bc. Informatika, VUT Brno',
                'description' => 'Bakalářská práce: Webový systém pro správu projektů.',
            ],
        ];
    }

    /** @return list<array{icon: string, title: string, description: string}> */
    private function getValues(): array
    {
        return [
            [
                'icon'        => '🎯',
                'title'       => 'Přesnost',
                'description' => 'Termíny jsou závazky, ne odhady.',
            ],
            [
                'icon'        => '🔍',
                'title'       => 'Transparentnost',
                'description' => 'Pravidelné reporty a otevřená komunikace.',
            ],
            [
                'icon'        => '⚡',
                'title'       => 'Výkon',
                'description' => 'Kód, který je rychlý a dobře otestovaný.',
            ],
            [
                'icon'        => '🤝',
                'title'       => 'Partnerství',
                'description' => 'Jsem součástí vašeho týmu, ne jen dodavatel.',
            ],
        ];
    }
}
