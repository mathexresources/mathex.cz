<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\AnalyticsRepository;
use App\Model\Database\SettingsRepository;
use App\Model\Service\LocalizationService;
use Contributte\Translation\Translator;
use Nette\Application\Helpers;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{
    protected string $siteTitle      = 'Mathex.cz';
    protected string $metaDescription = 'Freelance PHP / Nette vývojář. Vývoj webových aplikací, e-shopů a REST API na míru.';
    protected ?string $ogImage       = null;
    protected ?string $canonicalUrl  = null;
    protected string $locale         = 'cs';

    /** @var array<string, mixed>|null */
    protected ?array $jsonLd = null;

    /** @inject */
    public LocalizationService $localization;

    /** @inject */
    public AnalyticsRepository $analytics;

    /** @inject */
    public SettingsRepository $settings;

    /** @inject */
    public Translator $translator;

    public function formatTemplateFiles(): array
    {
        [$module, $presenter] = Helpers::splitName($this->getName());
        $dir = __DIR__ . '/../Templates';
        $subdir = $module ? "$dir/$module/$presenter" : "$dir/$presenter";
        return [
            "$subdir/{$this->getView()}.latte",
            "$subdir.{$this->getView()}.latte",
        ];
    }

    public function formatLayoutTemplateFiles(): array
    {
        [$module, $presenter] = Helpers::splitName($this->getName());
        $dir = __DIR__ . '/../Templates';
        $moduleDir = $module ? "$dir/$module" : $dir;
        return [
            "$moduleDir/@layout.latte",
            "$dir/@layout.latte",
        ];
    }

    protected function startup(): void
    {
        parent::startup();

        // /en/ routes pass locale=en as a route parameter
        $urlLocale = $this->getParameter('locale');
        if (is_string($urlLocale) && in_array($urlLocale, ['cs', 'en'], true)) {
            $this->localization->setLocale($urlLocale, persistCookie: true);
        }

        $this->locale = $this->localization->getLocale();

        // Sync translator locale
        $this->translator->setLocale($this->locale);
    }

    protected function beforeRender(): void
    {
        parent::beforeRender();

        $request = $this->getHttpRequest();

        // Track public page views (GET only, skip admin)
        if (
            $request->getMethod() === 'GET'
            && !str_starts_with($this->getName(), 'Admin:')
            && !str_starts_with($request->getUrl()->getPath(), '/admin')
        ) {
            try {
                $ip = $request->getRemoteAddress();
                $this->analytics->record(
                    $request->getUrl()->getPath(),
                    $request->getHeader('Referer'),
                    $request->getHeader('User-Agent'),
                    $ip ? hash('sha256', $ip) : null,
                    session_id() !== '' ? hash('sha256', session_id()) : null,
                );
            } catch (\Throwable) {
                // Analytics failure must not break pages
            }
        }

        // Compute hreflang URLs from current path
        $baseUrl = rtrim($request->getUrl()->getBaseUrl(), '/');
        $path    = $request->getUrl()->getPath();

        if (str_starts_with($path, '/en')) {
            $csPath = substr($path, 3) ?: '/';
            $enPath = $path;
        } else {
            $csPath = $path;
            $enPath = '/en' . $path;
        }

        $this->template->siteTitle       = $this->siteTitle;
        $this->template->basePath        = $request->getUrl()->getBasePath();
        $this->template->user            = $this->getUser();
        $this->template->metaDescription = $this->metaDescription;
        $this->template->ogImage         = $this->ogImage;
        $this->template->canonicalUrl    = $this->canonicalUrl;
        $this->template->jsonLd          = $this->jsonLd;
        $this->template->locale          = $this->locale;
        $this->template->hreflangCsUrl   = $baseUrl . $csPath;
        $this->template->hreflangEnUrl   = $baseUrl . $enPath;
        $this->template->gaTag           = $this->settings->get('ga_tag', '');
    }
}
