<?php

declare(strict_types=1);

use App\Model\Service\MarkdownService;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('toHtml converts bold markdown', function () {
    $svc = new MarkdownService();
    Assert::match('%A%<strong>bold</strong>%A%', $svc->toHtml('**bold**'));
});

test('toHtml converts italic markdown', function () {
    $svc = new MarkdownService();
    Assert::match('%A%<em>italic</em>%A%', $svc->toHtml('*italic*'));
});

test('toHtml converts headings', function () {
    $svc = new MarkdownService();
    Assert::match('%A%<h1>%A%Title%A%</h1>%A%', $svc->toHtml('# Title'));
});

test('toHtml converts links', function () {
    $svc = new MarkdownService();
    $html = $svc->toHtml('[link](https://example.com)');
    Assert::match('%A%href="https://example.com"%A%', $html);
});

test('toHtml strips unsafe inline HTML by default', function () {
    $svc = new MarkdownService();
    $html = $svc->toHtml('<script>alert(1)</script>');
    Assert::false((bool) strpos($html, '<script>'));
});

test('toBlogHtml allows raw HTML passthrough', function () {
    $svc = new MarkdownService();
    $html = $svc->toBlogHtml('<strong>raw</strong>');
    Assert::match('%A%<strong>raw</strong>%A%', $html);
});

test('toText strips all HTML tags', function () {
    $svc = new MarkdownService();
    $text = $svc->toText('**Hello** *world*');
    Assert::false((bool) strpos($text, '<'));
    Assert::contains('Hello', $text);
    Assert::contains('world', $text);
});

test('estimateReadingTime returns at least 1', function () {
    $svc = new MarkdownService();
    Assert::same(1, $svc->estimateReadingTime('Hi'));
});

test('estimateReadingTime scales with word count', function () {
    $svc = new MarkdownService();
    $words = implode(' ', array_fill(0, 400, 'word'));
    Assert::same(2, $svc->estimateReadingTime($words));
});

test('toHtmlSafe is equivalent to toHtml', function () {
    $svc = new MarkdownService();
    Assert::same($svc->toHtml('**test**'), $svc->toHtmlSafe('**test**'));
});
