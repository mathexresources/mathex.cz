<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Model\Service\ImageUploadService;
use Nette\Application\UI\Form;

final class SettingsPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly ImageUploadService $imageUpload,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->pageTitle = 'Nastavení';
        $all = $this->settings->getAll();
        $this->template->settings = $all;

        $this['settingsForm']->setDefaults($all);
    }

    protected function createComponentSettingsForm(): Form
    {
        $form = new Form();
        $form->addProtection();

        // === General ===
        $form->addText('site_name', 'Název webu:')->setMaxLength(255);
        $form->addText('tagline_cs', 'Tagline (CS):')->setMaxLength(500);
        $form->addText('tagline_en', 'Tagline (EN):')->setMaxLength(500);
        $form->addUpload('logo_file', 'Logo:')
            ->addCondition(Form::FILLED)
            ->addRule(Form::IMAGE, 'Povoleny jsou JPEG, PNG, GIF a WebP.');
        $form->addUpload('favicon_file', 'Favicon (PNG, 32×32):')
            ->addCondition(Form::FILLED)
            ->addRule(Form::MIME_TYPE, 'Povoleny jsou PNG a ICO.', ['image/png', 'image/x-icon']);

        // === About ===
        $form->addTextArea('bio_cs', 'Bio (CS):', null, 6)->setHtmlAttribute('rows', 6);
        $form->addTextArea('bio_en', 'Bio (EN):', null, 6)->setHtmlAttribute('rows', 6);
        $form->addUpload('cv_file', 'CV (PDF):')
            ->addCondition(Form::FILLED)
            ->addRule(Form::MIME_TYPE, 'Povoleny jsou PDF soubory.', 'application/pdf');
        $form->addUpload('profile_photo_file', 'Profilová fotografie:')
            ->addCondition(Form::FILLED)
            ->addRule(Form::IMAGE, 'Povoleny jsou JPEG, PNG, GIF a WebP.');
        $form->addText('github_url', 'GitHub URL:')->setMaxLength(500)->addRule(Form::URL, 'Zadejte platnou URL.');
        $form->addText('linkedin_url', 'LinkedIn URL:')->setMaxLength(500)->addRule(Form::URL, 'Zadejte platnou URL.');
        $form->addText('twitter_url', 'Twitter/X URL:')->setMaxLength(500)->addRule(Form::URL, 'Zadejte platnou URL.');
        $form->addText('instagram_url', 'Instagram URL:')->setMaxLength(500)->addRule(Form::URL, 'Zadejte platnou URL.');

        // === Contact ===
        $form->addEmail('contact_email', 'Kontaktní e-mail:')->setMaxLength(255);
        $form->addText('contact_phone', 'Telefon:')->setMaxLength(30);
        $form->addText('contact_location', 'Místo:')->setMaxLength(255);
        $form->addText('working_hours', 'Pracovní hodiny:')->setMaxLength(100);
        $form->addTextArea('google_maps_embed', 'Google Maps embed kód:')->setHtmlAttribute('rows', 4);

        // === SEO ===
        $form->addTextArea('meta_description_cs', 'Meta popis (CS):', null, 4)->setMaxLength(300);
        $form->addTextArea('meta_description_en', 'Meta popis (EN):', null, 4)->setMaxLength(300);
        $form->addText('ga_tag', 'Google Analytics ID (G-XXXXXXXX):')->setMaxLength(50);
        $form->addText('seo_keywords', 'Klíčová slova:')->setMaxLength(500);

        // === Appearance ===
        $form->addText('accent_color', 'Akcentová barva (hex):')
            ->setDefaultValue('#6366f1')
            ->setMaxLength(20);
        $form->addSelect('dark_mode_default', 'Výchozí téma:', [
            'dark'  => 'Tmavé',
            'light' => 'Světlé',
        ]);

        // === Meetings ===
        $form->addText('meeting_available_times', 'Dostupné časy (oddělené čárkou):')
            ->setDefaultValue('09:00,10:00,11:00,13:00,14:00,15:00,16:00')
            ->setMaxLength(500);
        $form->addText('meeting_duration', 'Délka schůzky (minut):')
            ->setDefaultValue('60')
            ->setMaxLength(10);
        $form->addText('webhook_url', 'Webhook URL (Zapier / Make):')
            ->setMaxLength(500);
        $form->addCheckbox('google_calendar_enabled', 'Aktivovat Google Calendar');
        $form->addText('google_calendar_id', 'Google Calendar ID:')
            ->setMaxLength(255);
        $form->addTextArea('google_credentials_json', 'Google Service Account JSON:')
            ->setHtmlAttribute('rows', 6);

        $form->addSubmit('save', 'Uložit nastavení');
        $form->onSuccess[] = [$this, 'settingsFormSucceeded'];

        return $form;
    }

    public function settingsFormSucceeded(Form $form, \stdClass $values): void
    {
        $data = (array) $values;

        // Handle file uploads
        $fileUploads = [
            'logo_file'          => ['key' => 'logo_url', 'dir' => 'settings'],
            'favicon_file'       => ['key' => 'favicon_url', 'dir' => 'settings', 'noThumb' => true],
            'profile_photo_file' => ['key' => 'profile_photo_url', 'dir' => 'settings'],
        ];

        foreach ($fileUploads as $field => $config) {
            if (isset($data[$field]) && $data[$field]->isOk()) {
                try {
                    $thumbWidth = ($config['noThumb'] ?? false) ? null : 800;
                    $uploaded   = $this->imageUpload->upload($data[$field], $config['dir'], $thumbWidth);
                    $this->settings->set($config['key'], $uploaded['url']);
                } catch (\RuntimeException $e) {
                    $form->addError('Chyba při nahrávání souboru: ' . $e->getMessage());
                }
            }
            unset($data[$field]);
        }

        // Handle CV PDF upload separately (not an image)
        if (isset($data['cv_file']) && $data['cv_file']->isOk()) {
            try {
                $cvUrl = $this->imageUpload->uploadFile($data['cv_file'], 'settings');
                $this->settings->set('cv_url', $cvUrl);
            } catch (\RuntimeException $e) {
                $form->addError('Chyba při nahrávání CV: ' . $e->getMessage());
            }
        }
        unset($data['cv_file']);

        // Save all text settings
        $skip = ['_token_', 'save'];
        foreach ($data as $key => $value) {
            if (in_array($key, $skip, true)) {
                continue;
            }
            $this->settings->set($key, (string) $value);
        }

        $this->flashMessage('Nastavení bylo uloženo.', 'success');
        $this->redirect('this');
    }
}
