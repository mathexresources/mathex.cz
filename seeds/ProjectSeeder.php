<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class ProjectSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return [];
    }

    public function run(): void
    {
        $this->table('projects')->truncate();

        $projects = [
            [
                'slug'               => 'eshop-modni-dum-praha',
                'title'              => 'Inteligentní e-shop pro Módní dům Praha',
                'description_cs'     => 'Komplexní e-commerce řešení s napojením na GoPay, Zásilkovnu a účetní systém Pohoda. Zvýšení konverzního poměru o 34 % díky optimalizaci checkout procesu.',
                'description_en'     => 'Full-featured e-commerce solution integrated with GoPay, Zásilkovna, and the Pohoda accounting system. Checkout optimisation increased conversion rate by 34%.',
                'technologies'       => json_encode(['PHP 8.2', 'Nette Framework', 'MySQL 8', 'Redis', 'Vue.js 3', 'Tailwind CSS', 'GoPay API', 'Pohoda XML', 'Docker']),
                'cover_image'        => '/assets/images/projects/modni-dum-cover.webp',
                'gallery_images'     => json_encode([
                    '/assets/images/projects/modni-dum-1.webp',
                    '/assets/images/projects/modni-dum-2.webp',
                    '/assets/images/projects/modni-dum-3.webp',
                ]),
                'project_url'        => null,
                'github_url'         => null,
                'case_study_content' => '<h2>Zadání</h2><p>Módní dům Praha provozoval zastaralý e-shop na pronajatém řešení s omezenými možnostmi úprav. Potřebovali platformu na míru s plnou kontrolou nad designem, výkonem a integrací s interními systémy.</p><h2>Řešení</h2><p>Navrhl jsem a implementoval e-shop v Nette Frameworku s plnohodnotnou správou produktů, variant, skladových zásob a objednávek. Klíčovou součástí bylo napojení na platební bránu GoPay a dopravce Zásilkovna a PPL přes jejich REST API. Účetní doklady se automaticky propisují do systému Pohoda přes XML import.</p><h2>Výsledky</h2><ul><li>Konverzní poměr +34 % po optimalizaci checkoutu</li><li>Průměrný čas načítání stránek snížen na 0,8 s (byl 3,2 s)</li><li>Automatizace zpracování objednávek ušetří 15 h/týdně manuální práce</li></ul>',
                'client_name'        => 'Módní dům Praha s.r.o.',
                'year'               => 2023,
                'category'           => 'eshop',
                'featured'           => 1,
                'order_index'        => 1,
                'published'          => 1,
                'created_at'         => '2024-01-15 10:00:00',
                'updated_at'         => '2024-01-15 10:00:00',
            ],
            [
                'slug'               => 'rezervacni-system-medcentrum-brno',
                'title'              => 'Rezervační systém MedCentrum Brno',
                'description_cs'     => 'Online rezervační systém pro zdravotní kliniku s 12 lékaři. Automatické SMS a e-mailové připomínky, propojení s Google Calendar, správa ordinačních hodin.',
                'description_en'     => 'Online booking system for a healthcare clinic with 12 doctors. Automatic SMS and email reminders, Google Calendar sync, and appointment management.',
                'technologies'       => json_encode(['PHP 8.2', 'Nette Framework', 'MySQL 8', 'Alpine.js', 'Twilio SMS', 'Google Calendar API', 'Bootstrap 5', 'Docker']),
                'cover_image'        => '/assets/images/projects/medcentrum-cover.webp',
                'gallery_images'     => json_encode([
                    '/assets/images/projects/medcentrum-1.webp',
                    '/assets/images/projects/medcentrum-2.webp',
                ]),
                'project_url'        => null,
                'github_url'         => null,
                'case_study_content' => '<h2>Zadání</h2><p>MedCentrum Brno přijímalo rezervace výhradně telefonicky, což způsobovalo přetížení recepce a nespokojené pacienty čekající na linku. Cílem bylo snížit zátěž recepce o 60 % a umožnit online objednávání 24/7.</p><h2>Řešení</h2><p>Systém umožňuje pacientům vybrat lékaře, typ vyšetření a dostupný termín. 24 hodin před návštěvou odejde automatická SMS připomínka přes Twilio. Lékaři mají svůj kalendář synchronizovaný s Google Calendar. Recepce má přehledný backoffice s denním plánem.</p><h2>Výsledky</h2><ul><li>Objem telefonátů na recepci klesl o 58 %</li><li>Průměrné no-show rate kleslo z 22 % na 8 % díky SMS připomínkám</li><li>Spokojenost pacientů (NPS) vzrostla o 28 bodů</li></ul>',
                'client_name'        => 'MedCentrum Brno a.s.',
                'year'               => 2023,
                'category'           => 'webapp',
                'featured'           => 1,
                'order_index'        => 2,
                'published'          => 1,
                'created_at'         => '2024-02-01 10:00:00',
                'updated_at'         => '2024-02-01 10:00:00',
            ],
            [
                'slug'               => 'rest-api-fleettrack',
                'title'              => 'REST API pro aplikaci FleetTrack',
                'description_cs'     => 'Škálovatelné REST API pro mobilní aplikaci pro správu vozového parku. JWT autentizace, rate limiting přes Redis, real-time poloha vozidel přes WebSocket.',
                'description_en'     => 'Scalable REST API for a fleet management mobile app. JWT authentication, Redis rate limiting, and real-time vehicle location via WebSocket.',
                'technologies'       => json_encode(['PHP 8.2', 'Nette Framework', 'MySQL 8', 'Redis', 'JWT', 'WebSocket (Ratchet)', 'OpenAPI 3.0', 'Docker', 'GitHub Actions']),
                'cover_image'        => '/assets/images/projects/fleettrack-cover.webp',
                'gallery_images'     => json_encode([
                    '/assets/images/projects/fleettrack-1.webp',
                    '/assets/images/projects/fleettrack-2.webp',
                    '/assets/images/projects/fleettrack-3.webp',
                ]),
                'project_url'        => null,
                'github_url'         => null,
                'case_study_content' => '<h2>Zadání</h2><p>FleetTrack Technologies potřebovaly backend pro mobilní aplikaci (iOS + Android), která zobrazuje polohu vozidel v reálném čase, spravuje jízdy a generuje reporty pro správce flotily.</p><h2>Řešení</h2><p>Navrhl jsem REST API dle OpenAPI 3.0 specifikace s JWT autentizací. Real-time poloha vozidel se přenáší přes WebSocket server postavený na Ratchet. Kritické endpointy mají rate limiting přes Redis. Kompletní Swagger dokumentace a Postman kolekce byly součástí dodávky.</p><h2>Výsledky</h2><ul><li>API zvládá 50 000 požadavků/hod bez degradace výkonu</li><li>Průměrná latence endpointů pod 80 ms</li><li>99,8% uptime za první 3 měsíce provozu</li></ul>',
                'client_name'        => 'FleetTrack Technologies s.r.o.',
                'year'               => 2024,
                'category'           => 'api',
                'featured'           => 1,
                'order_index'        => 3,
                'published'          => 1,
                'created_at'         => '2024-03-01 10:00:00',
                'updated_at'         => '2024-03-01 10:00:00',
            ],
            [
                'slug'               => 'hr-portal-investgroup',
                'title'              => 'HR portál InvestGroup Czech',
                'description_cs'     => 'Interní HR systém pro 240 zaměstnanců: správa docházky, žádosti o dovolenou, výplatní pásky a firemní wiki. Přihlašování přes LDAP / Active Directory.',
                'description_en'     => 'Internal HR system for 240 employees: attendance tracking, leave requests, pay slips, and company wiki. LDAP / Active Directory single sign-on.',
                'technologies'       => json_encode(['PHP 8.1', 'Nette Framework', 'MySQL 8', 'LDAP', 'Bootstrap 5', 'Chart.js', 'Docker']),
                'cover_image'        => '/assets/images/projects/investgroup-cover.webp',
                'gallery_images'     => json_encode([
                    '/assets/images/projects/investgroup-1.webp',
                    '/assets/images/projects/investgroup-2.webp',
                ]),
                'project_url'        => null,
                'github_url'         => null,
                'case_study_content' => '<h2>Zadání</h2><p>InvestGroup Czech spravovala docházku a dovolenky v Excelu, výplatní pásky v PDF e-mailem. HR oddělení hledalo centralizované řešení s napojením na stávající Active Directory.</p><h2>Řešení</h2><p>Vybudoval jsem HR portál s SSO přes LDAP/AD. Zaměstnanci žádají o dovolenou online, manažeři schvalují jedním klikem. Docházkový modul se synchronizuje s docházkovou čtečkou. Výplatní pásky jsou přístupné online po přihlášení.</p><h2>Výsledky</h2><ul><li>HR oddělení ušetří 20 h/měsíc administrativní práce</li><li>Papírové žádanky o dovolenou nahrazeny 100% digitálním procesem</li><li>Průměrná doba schválení žádosti o dovolenou: 4 min (byl 2 dny)</li></ul>',
                'client_name'        => 'InvestGroup Czech s.r.o.',
                'year'               => 2022,
                'category'           => 'webapp',
                'featured'           => 0,
                'order_index'        => 4,
                'published'          => 1,
                'created_at'         => '2024-04-01 10:00:00',
                'updated_at'         => '2024-04-01 10:00:00',
            ],
            [
                'slug'               => 'elearning-platforma-edunova',
                'title'              => 'E-learningová platforma EduNova',
                'description_cs'     => 'LMS platforma pro online kurzy s videoplayer, kvízy, certifikáty a napojením na Stripe pro předplatné. Přes 1 500 aktivních studentů.',
                'description_en'     => 'LMS platform for online courses featuring a video player, quizzes, certificates, and Stripe subscription billing. Over 1,500 active students.',
                'technologies'       => json_encode(['PHP 8.2', 'Nette Framework', 'MySQL 8', 'Redis', 'Vue.js 3', 'Stripe', 'AWS S3', 'FFmpeg', 'Docker', 'GitHub Actions']),
                'cover_image'        => '/assets/images/projects/edunova-cover.webp',
                'gallery_images'     => json_encode([
                    '/assets/images/projects/edunova-1.webp',
                    '/assets/images/projects/edunova-2.webp',
                    '/assets/images/projects/edunova-3.webp',
                ]),
                'project_url'        => null,
                'github_url'         => null,
                'case_study_content' => '<h2>Zadání</h2><p>EduNova nabízela kurzy přes Teachable, ale narážela na limity platformy – žádná čeština v UI, omezená customizace a vysoké provize. Chtěli vlastní řešení s plnou kontrolou.</p><h2>Řešení</h2><p>Vybudoval jsem LMS s modulárním systémem kurzů (videa na AWS S3, transkódování přes FFmpeg). Studenti sledují progres, plní kvízy a po absolvování obdrží certifikát ve formátu PDF. Platba probíhá přes Stripe (jednorázová platba nebo předplatné).</p><h2>Výsledky</h2><ul><li>Úspora 18 000 Kč/měsíc na poplatcích za Teachable</li><li>1 500+ aktivních studentů v prvním roce</li><li>NPS studentů: 72 (benchmark v oboru: 45)</li></ul>',
                'client_name'        => 'EduNova s.r.o.',
                'year'               => 2024,
                'category'           => 'webapp',
                'featured'           => 1,
                'order_index'        => 5,
                'published'          => 1,
                'created_at'         => '2024-05-01 10:00:00',
                'updated_at'         => '2024-05-01 10:00:00',
            ],
        ];

        $this->table('projects')->insert($projects)->saveData();
        $this->output->writeln('  <info>ProjectSeeder</info>: ' . count($projects) . ' projects inserted');
    }
}
