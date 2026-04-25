<?php

declare(strict_types=1);

namespace App\Model\Service;

use Nette\Database\Table\ActiveRow;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Http\Session;
use Nette\Http\SessionSection;

final class LocalizationService
{
    private const SESSION_KEY  = 'locale';
    private const COOKIE_NAME  = 'lang';
    private const DEFAULT_LANG = 'cs';
    private const SUPPORTED    = ['cs', 'en'];
    private const COOKIE_TTL   = 31536000; // 1 year in seconds

    private SessionSection $section;

    public function __construct(
        private readonly IRequest  $httpRequest,
        private readonly IResponse $httpResponse,
        Session $session,
    ) {
        $this->section = $session->getSection('localization');
    }

    public function getLocale(): string
    {
        // Cookie takes priority (persists across sessions)
        $cookie = $this->httpRequest->getCookie(self::COOKIE_NAME);
        if (is_string($cookie) && in_array($cookie, self::SUPPORTED, true)) {
            return $cookie;
        }

        return $this->section->get(self::SESSION_KEY) ?? self::DEFAULT_LANG;
    }

    public function setLocale(string $locale, bool $persistCookie = false): void
    {
        if (!in_array($locale, self::SUPPORTED, true)) {
            throw new \InvalidArgumentException("Unsupported locale '{$locale}'. Supported: " . implode(', ', self::SUPPORTED));
        }

        $this->section->set(self::SESSION_KEY, $locale);

        if ($persistCookie) {
            $this->httpResponse->setCookie(
                self::COOKIE_NAME,
                $locale,
                time() + self::COOKIE_TTL,
                '/',
            );
        }
    }

    public function isCs(): bool
    {
        return $this->getLocale() === 'cs';
    }

    public function isEn(): bool
    {
        return $this->getLocale() === 'en';
    }

    public function getSupportedLocales(): array
    {
        return self::SUPPORTED;
    }

    /**
     * Returns the localized column value from a row for the given base column name.
     * E.g. getField($row, 'title') returns $row->title_cs or $row->title_en.
     * Falls back to the other language when the current language value is empty.
     */
    public function getField(ActiveRow $row, string $field): mixed
    {
        $locale    = $this->getLocale();
        $localized = $row->{$field . '_' . $locale} ?? null;

        if ($localized !== null && $localized !== '') {
            return $localized;
        }

        // Fall back to default language
        if ($locale !== self::DEFAULT_LANG) {
            return $row->{$field . '_' . self::DEFAULT_LANG} ?? null;
        }

        return null;
    }

    /**
     * Returns an array of localized fields from a row.
     *
     * @param list<string> $fields
     * @return array<string, mixed>
     */
    public function getFields(ActiveRow $row, array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            $result[$field] = $this->getField($row, $field);
        }
        return $result;
    }

    /**
     * Detects preferred language from the Accept-Language header.
     */
    public function detectFromBrowser(): string
    {
        $header = $this->httpRequest->getHeader('Accept-Language') ?? '';
        if (str_starts_with(strtolower($header), 'cs')) {
            return 'cs';
        }
        if (str_starts_with(strtolower($header), 'en')) {
            return 'en';
        }
        return self::DEFAULT_LANG;
    }
}
