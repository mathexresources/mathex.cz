<?php

declare(strict_types=1);

namespace App\Model\Mail;

use Latte\Engine;

/**
 * Renders email Latte templates without Nette application macros.
 * Uses a fresh Latte engine so {link}, {snippet} etc. are not available.
 */
final class MailTemplateRenderer
{
    private readonly Engine $engine;

    public function __construct(
        private readonly string $templateDir,
        string $cacheDir,
    ) {
        $this->engine = new Engine();
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $this->engine->setTempDirectory($cacheDir);

        // Safe nl2br: HTML-escape then add <br> tags
        $this->engine->addFilter('nl2brEscape', static function (string $s): string {
            return nl2br(htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        });
    }

    public function render(string $template, array $params = []): string
    {
        return $this->engine->renderToString(
            $this->templateDir . '/' . $template,
            $params,
        );
    }
}
