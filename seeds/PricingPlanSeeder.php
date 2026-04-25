<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class PricingPlanSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['ServiceSeeder'];
    }

    public function run(): void
    {
        $this->table('pricing_plans')->truncate();

        // Resolve service IDs by slug
        $svcRows = $this->fetchAll('SELECT id, slug FROM services');
        $svcId   = array_column($svcRows, 'id', 'slug');

        $plans = [
            // ─── Webové aplikace ─────────────────────────────────────────────
            [
                'service_id'   => $svcId['webove-aplikace'] ?? null,
                'name_cs'      => 'Firemní web',
                'name_en'      => 'Corporate Website',
                'price_from'   => 35000.00,
                'price_to'     => 80000.00,
                'currency'     => 'CZK',
                'billing_type' => 'fixed',
                'features_cs'  => json_encode([
                    'Až 10 stránek + blog',
                    'Responsivní design',
                    'Základní SEO nastavení',
                    'Kontaktní formulář',
                    'GDPR cookies souhlas',
                    '3 měsíce bezplatné podpory',
                ]),
                'features_en'  => json_encode([
                    'Up to 10 pages + blog',
                    'Responsive design',
                    'Basic SEO setup',
                    'Contact form',
                    'GDPR cookie consent',
                    '3 months free support',
                ]),
                'highlighted'  => 0,
                'active'       => 1,
                'order_index'  => 1,
            ],
            [
                'service_id'   => $svcId['webove-aplikace'] ?? null,
                'name_cs'      => 'Webová aplikace',
                'name_en'      => 'Web Application',
                'price_from'   => 80000.00,
                'price_to'     => 250000.00,
                'currency'     => 'CZK',
                'billing_type' => 'fixed',
                'features_cs'  => json_encode([
                    'Správa uživatelů a rolí',
                    'Admin panel na míru',
                    'Napojení na API třetích stran',
                    'Redis cache a sessions',
                    'PHPUnit testy',
                    'Docker + CI/CD pipeline',
                    '6 měsíců bezplatné podpory',
                ]),
                'features_en'  => json_encode([
                    'User and role management',
                    'Custom admin panel',
                    'Third-party API integrations',
                    'Redis cache and sessions',
                    'PHPUnit tests',
                    'Docker + CI/CD pipeline',
                    '6 months free support',
                ]),
                'highlighted'  => 1,
                'active'       => 1,
                'order_index'  => 2,
            ],
            // ─── E-shopy ─────────────────────────────────────────────────────
            [
                'service_id'   => $svcId['eshopy'] ?? null,
                'name_cs'      => 'Starter e-shop',
                'name_en'      => 'Starter E-shop',
                'price_from'   => 50000.00,
                'price_to'     => 100000.00,
                'currency'     => 'CZK',
                'billing_type' => 'fixed',
                'features_cs'  => json_encode([
                    'Až 500 produktů',
                    '1 platební brána (GoPay nebo Stripe)',
                    '2 dopravci',
                    'Heureka / Zboží.cz feed',
                    'Základní správa objednávek',
                    '3 měsíce bezplatné podpory',
                ]),
                'features_en'  => json_encode([
                    'Up to 500 products',
                    '1 payment gateway (GoPay or Stripe)',
                    '2 couriers',
                    'Heureka / Zboží.cz feed',
                    'Basic order management',
                    '3 months free support',
                ]),
                'highlighted'  => 0,
                'active'       => 1,
                'order_index'  => 3,
            ],
            [
                'service_id'   => $svcId['eshopy'] ?? null,
                'name_cs'      => 'Pokročilý e-shop',
                'name_en'      => 'Advanced E-shop',
                'price_from'   => 100000.00,
                'price_to'     => 300000.00,
                'currency'     => 'CZK',
                'billing_type' => 'fixed',
                'features_cs'  => json_encode([
                    'Neomezený počet produktů',
                    'Všechny platební brány',
                    'Napojení na Pohoda / Money S3',
                    'Věrnostní program a slevové kódy',
                    'Pokročilá analytika',
                    'Multi-currency a multi-language',
                    '12 měsíců bezplatné podpory',
                ]),
                'features_en'  => json_encode([
                    'Unlimited products',
                    'All payment gateways',
                    'Pohoda / Money S3 integration',
                    'Loyalty programme and discount codes',
                    'Advanced analytics',
                    'Multi-currency and multi-language',
                    '12 months free support',
                ]),
                'highlighted'  => 1,
                'active'       => 1,
                'order_index'  => 4,
            ],
            // ─── REST API ─────────────────────────────────────────────────────
            [
                'service_id'   => $svcId['rest-api'] ?? null,
                'name_cs'      => 'REST API',
                'name_en'      => 'REST API',
                'price_from'   => 40000.00,
                'price_to'     => 200000.00,
                'currency'     => 'CZK',
                'billing_type' => 'fixed',
                'features_cs'  => json_encode([
                    'Návrh dle OpenAPI 3.0',
                    'JWT autentizace',
                    'Rate limiting',
                    'Swagger dokumentace',
                    'Postman kolekce',
                    'PHPUnit a integrační testy',
                ]),
                'features_en'  => json_encode([
                    'OpenAPI 3.0 design',
                    'JWT authentication',
                    'Rate limiting',
                    'Swagger documentation',
                    'Postman collections',
                    'PHPUnit and integration tests',
                ]),
                'highlighted'  => 0,
                'active'       => 1,
                'order_index'  => 5,
            ],
            // ─── Konzultace ──────────────────────────────────────────────────
            [
                'service_id'   => $svcId['konzultace'] ?? null,
                'name_cs'      => 'Hodinová konzultace',
                'name_en'      => 'Hourly Consulting',
                'price_from'   => 1800.00,
                'price_to'     => 2500.00,
                'currency'     => 'CZK',
                'billing_type' => 'hourly',
                'features_cs'  => json_encode([
                    'Code review (GitHub / GitLab)',
                    'Bezpečnostní audit',
                    'Architektonické poradenství',
                    'Mentoring',
                    'Zápis z konzultace e-mailem',
                ]),
                'features_en'  => json_encode([
                    'Code review (GitHub / GitLab)',
                    'Security audit',
                    'Architecture consulting',
                    'Mentoring',
                    'Written follow-up by e-mail',
                ]),
                'highlighted'  => 0,
                'active'       => 1,
                'order_index'  => 6,
            ],
        ];

        $this->table('pricing_plans')->insert($plans)->saveData();
        $this->output->writeln('  <info>PricingPlanSeeder</info>: ' . count($plans) . ' plans inserted');
    }
}
