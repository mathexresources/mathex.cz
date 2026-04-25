<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class SiteSettingsSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return [];
    }

    public function run(): void
    {
        $this->table('site_settings')->truncate();

        // helper: build a row
        $s = static fn(string $key, ?string $value, string $type, string $label, string $group): array => compact('key', 'value', 'type', 'label', 'group');

        $settings = [
            // ─── General ──────────────────────────────────────────────────────
            $s('site_name',               'Mathex.cz',                           'text',     'Název webu',                      'general'),
            $s('site_tagline_cs',         'Freelance PHP vývojář | Praha',        'text',     'Motto (CS)',                       'general'),
            $s('site_tagline_en',         'Freelance PHP Developer | Prague',     'text',     'Motto (EN)',                       'general'),
            $s('site_description_cs',     'Vyvíjím webové aplikace, e-shopy a REST API v PHP a Nette Frameworku. Pracuji s firmami a startupy po celé ČR.', 'textarea', 'Popis webu (CS)', 'general'),
            $s('site_description_en',     'I build web applications, e-shops and REST APIs in PHP and Nette Framework. I work with companies and startups across the Czech Republic.', 'textarea', 'Popis webu (EN)', 'general'),
            $s('site_author',             'Mathex',                               'text',     'Jméno autora',                    'general'),
            $s('site_logo',               null,                                   'image',    'Logo (URL)',                       'general'),
            $s('site_og_image',           '/assets/images/og-image.webp',         'image',    'Open Graph obrázek',              'general'),
            $s('google_analytics_id',     null,                                   'text',     'Google Analytics ID (GA4)',        'general'),

            // ─── Contact ──────────────────────────────────────────────────────
            $s('contact_email',           'info@mathex.cz',                       'text',     'Kontaktní e-mail',                'contact'),
            $s('contact_phone',           '+420 721 123 456',                     'text',     'Telefon',                         'contact'),
            $s('contact_location',        'Praha, Česká republika',               'text',     'Lokalita',                        'contact'),
            $s('contact_working_hours',   'Po–Pá  9:00–17:00',                    'text',     'Pracovní doba',                   'contact'),
            $s('response_time_promise',   'Odpovídám zpravidla do 24 hodin.',     'text',     'Slib odpovědi',                   'contact'),

            // ─── Social links ─────────────────────────────────────────────────
            $s('social_github',           'https://github.com/mathexresources',   'text',     'GitHub URL',                      'social'),
            $s('social_linkedin',         'https://linkedin.com/in/mathex',       'text',     'LinkedIn URL',                    'social'),
            $s('social_twitter',          null,                                   'text',     'Twitter / X URL',                 'social'),
            $s('social_youtube',          null,                                   'text',     'YouTube URL',                     'social'),

            // ─── Availability ─────────────────────────────────────────────────
            $s('availability_status',     'available',                            'text',     'Dostupnost (available / busy / unavailable)', 'availability'),
            $s('availability_message_cs', 'Aktuálně přijímám nové projekty od října 2024.', 'textarea', 'Zpráva o dostupnosti (CS)', 'availability'),
            $s('availability_message_en', 'Currently accepting new projects from October 2024.', 'textarea', 'Zpráva o dostupnosti (EN)', 'availability'),
            $s('hourly_rate_czk',         '1 800',                                'text',     'Hodinová sazba (CZK)',            'availability'),

            // ─── Homepage ─────────────────────────────────────────────────────
            $s('hero_title_cs',           'Stavím webové aplikace na míru',       'text',     'Hero nadpis (CS)',                'homepage'),
            $s('hero_title_en',           'I Build Custom Web Applications',      'text',     'Hero nadpis (EN)',                'homepage'),
            $s('hero_subtitle_cs',        'PHP · Nette Framework · MySQL · Redis · Vue.js', 'text', 'Hero podnadpis (CS)',      'homepage'),
            $s('hero_subtitle_en',        'PHP · Nette Framework · MySQL · Redis · Vue.js', 'text', 'Hero podnadpis (EN)',      'homepage'),
            $s('about_text_cs',           'Jsem freelance PHP vývojář s více než 8 lety zkušeností. Specializuji se na Nette Framework, škálovatelné e-shopy a REST API. Pracuji s firmami i startupy napříč celou ČR – remote nebo osobně v Praze.', 'textarea', 'O mně (CS)', 'homepage'),
            $s('about_text_en',           'I\'m a freelance PHP developer with 8+ years of experience. I specialise in Nette Framework, scalable e-commerce, and REST APIs. I work with companies and startups across the Czech Republic – remote or in-person in Prague.', 'textarea', 'O mně (EN)', 'homepage'),
            $s('years_experience',        '8',                                    'text',     'Roky zkušeností',                'homepage'),
            $s('projects_completed',      '50+',                                  'text',     'Dokončených projektů',           'homepage'),
            $s('happy_clients',           '35+',                                  'text',     'Spokojených klientů',            'homepage'),

            // ─── SEO / Technical ──────────────────────────────────────────────
            $s('robots_txt',              "User-agent: *\nAllow: /",              'textarea', 'robots.txt obsah',               'technical'),
            $s('maintenance_mode',        '0',                                    'boolean',  'Režim údržby (1 = zapnuto)',     'technical'),
            $s('maintenance_message_cs',  'Web je dočasně nedostupný kvůli údržbě. Zkuste to prosím za chvíli.', 'textarea', 'Zpráva při údržbě (CS)', 'technical'),
        ];

        $this->table('site_settings')->insert($settings)->saveData();
        $this->output->writeln('  <info>SiteSettingsSeeder</info>: ' . count($settings) . ' settings inserted');
    }
}
