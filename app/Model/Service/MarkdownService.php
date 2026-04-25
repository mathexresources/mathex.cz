<?php

declare(strict_types=1);

namespace App\Model\Service;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

final class MarkdownService
{
    private MarkdownConverter $converter;
    private MarkdownConverter $converterBlog;

    public function __construct()
    {
        $safeEnv = new Environment(['html_input' => 'strip', 'allow_unsafe_links' => false, 'max_nesting_level' => 100]);
        $safeEnv->addExtension(new CommonMarkCoreExtension());
        $safeEnv->addExtension(new GithubFlavoredMarkdownExtension());
        $this->converter = new MarkdownConverter($safeEnv);

        // Admin-authored blog content: passthrough raw HTML from seed data / pasted content
        $blogEnv = new Environment(['html_input' => 'allow', 'allow_unsafe_links' => false, 'max_nesting_level' => 100]);
        $blogEnv->addExtension(new CommonMarkCoreExtension());
        $blogEnv->addExtension(new GithubFlavoredMarkdownExtension());
        $this->converterBlog = new MarkdownConverter($blogEnv);
    }

    public function toHtml(string $markdown): string
    {
        return $this->converter->convert($markdown)->getContent();
    }

    /** For admin-authored blog content: allows existing HTML to pass through unchanged. */
    public function toBlogHtml(string $markdown): string
    {
        return $this->converterBlog->convert($markdown)->getContent();
    }

    public function toHtmlSafe(string $markdown): string
    {
        return $this->toHtml($markdown);
    }

    /** Strips markdown syntax and returns plain text (useful for meta descriptions). */
    public function toText(string $markdown): string
    {
        $html = $this->toHtml($markdown);
        return strip_tags($html);
    }

    public function estimateReadingTime(string $markdown): int
    {
        $text  = $this->toText($markdown);
        $words = str_word_count($text);
        return max(1, (int) ceil($words / 200));
    }
}
