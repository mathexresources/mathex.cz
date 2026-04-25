<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class TestimonialSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['ProjectSeeder'];
    }

    public function run(): void
    {
        $this->table('testimonials')->truncate();

        // Resolve project IDs
        $rows   = $this->fetchAll('SELECT id, slug FROM projects');
        $projId = array_column($rows, 'id', 'slug');

        $testimonials = [
            [
                'client_name'     => 'Ing. Radek Novotný',
                'client_company'  => 'Módní dům Praha s.r.o.',
                'client_position' => 'CEO & zakladatel',
                'avatar_url'      => null,
                'text_cs'         => 'Spolupráce s Mathexem předčila naše očekávání. Doručil e-shop v termínu, kód je čistý a dobře zdokumentovaný. Konverzní poměr vzrostl o 34 % – to jsou reálné peníze navíc. Oceňuji také proaktivní komunikaci a ochotu vysvětlit technická rozhodnutí laicky.',
                'text_en'         => 'Working with Mathex exceeded our expectations. He delivered the e-shop on time, the code is clean and well-documented. Our conversion rate increased by 34% – that\'s real extra revenue. I also appreciate the proactive communication and willingness to explain technical decisions in plain language.',
                'rating'          => 5,
                'project_id'      => $projId['eshop-modni-dum-praha'] ?? null,
                'featured'        => 1,
                'order_index'     => 1,
                'active'          => 1,
            ],
            [
                'client_name'     => 'MUDr. Petra Horáková',
                'client_company'  => 'MedCentrum Brno a.s.',
                'client_position' => 'Ředitelka kliniky',
                'avatar_url'      => null,
                'text_cs'         => 'Rezervační systém nám změnil provoz kliniky. Recepce je nyní o 58 % méně vytížená telefony a pacienti jsou spokojení s možností se objednat kdykoliv. Mathex byl trpělivý, zodpovědný a vždy reagoval do hodiny. Rozhodně doporučuji.',
                'text_en'         => 'The booking system transformed our clinic operations. Reception is 58% less burdened by phone calls and patients love being able to book anytime. Mathex was patient, responsible, and always responded within the hour. Highly recommended.',
                'rating'          => 5,
                'project_id'      => $projId['rezervacni-system-medcentrum-brno'] ?? null,
                'featured'        => 1,
                'order_index'     => 2,
                'active'          => 1,
            ],
            [
                'client_name'     => 'Tomáš Kříž',
                'client_company'  => 'FleetTrack Technologies s.r.o.',
                'client_position' => 'CTO',
                'avatar_url'      => null,
                'text_cs'         => 'Hledali jsme PHP freelancera, který rozumí škálování a API designu. Mathex dodal REST API s OpenAPI dokumentací, Postman kolekcí a PHPUnit testy. Latence pod 80 ms a 99,8% uptime mluví za vše. Budeme spolupracovat i na dalším projektu.',
                'text_en'         => 'We were looking for a PHP freelancer who understands scaling and API design. Mathex delivered a REST API with OpenAPI documentation, Postman collections, and PHPUnit tests. Sub-80ms latency and 99.8% uptime speak for themselves. We\'ll work together on the next project too.',
                'rating'          => 5,
                'project_id'      => $projId['rest-api-fleettrack'] ?? null,
                'featured'        => 1,
                'order_index'     => 3,
                'active'          => 1,
            ],
            [
                'client_name'     => 'Bc. Jana Malá',
                'client_company'  => 'InvestGroup Czech s.r.o.',
                'client_position' => 'HR manažerka',
                'avatar_url'      => null,
                'text_cs'         => 'HR portál šetří HR oddělení 20 hodin měsíčně. Zaměstnanci si pochvalují, že vše najdou na jednom místě. Mathex byl schopný se rychle zorientovat v naší stávající infrastruktuře a navrhnout řešení, které do ní bezproblémově zapadlo.',
                'text_en'         => 'The HR portal saves our HR department 20 hours a month. Employees appreciate having everything in one place. Mathex quickly got to grips with our existing infrastructure and proposed a solution that fitted in seamlessly.',
                'rating'          => 5,
                'project_id'      => $projId['hr-portal-investgroup'] ?? null,
                'featured'        => 0,
                'order_index'     => 4,
                'active'          => 1,
            ],
            [
                'client_name'     => 'Martin Blaha',
                'client_company'  => 'EduNova s.r.o.',
                'client_position' => 'Produktový manažer',
                'avatar_url'      => null,
                'text_cs'         => 'Mathex navrhl a postavil kompletní LMS platformu od nuly. Ušetřili jsme 18 000 Kč měsíčně oproti Teachable a máme plnou kontrolu nad platformou. Kód je přehledný, testy pokrývají kritické cesty a předání projektu proběhlo hladce.',
                'text_en'         => 'Mathex designed and built a complete LMS platform from scratch. We save 18,000 CZK per month compared to Teachable and have full control over the platform. The code is clean, tests cover critical paths, and the handover went smoothly.',
                'rating'          => 5,
                'project_id'      => $projId['elearning-platforma-edunova'] ?? null,
                'featured'        => 1,
                'order_index'     => 5,
                'active'          => 1,
            ],
        ];

        $this->table('testimonials')->insert($testimonials)->saveData();
        $this->output->writeln('  <info>TestimonialSeeder</info>: ' . count($testimonials) . ' testimonials inserted');
    }
}
