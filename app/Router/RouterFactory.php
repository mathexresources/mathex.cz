<?php

declare(strict_types=1);

namespace App\Router;

use Nette\Application\Routers\RouteList;

final class RouterFactory
{
    public static function createRouter(): RouteList
    {
        $router = new RouteList;

        // Admin module
        $admin = new RouteList('Admin');
        $admin->addRoute('admin/<presenter>[/<action>][/<id \d+>]', 'Dashboard:default');
        $router->add($admin);

        // ── Health check ──────────────────────────────────────────────────────
        $router->addRoute('health', 'Health:default');

        // ── SEO / feed files (must come before wildcard blog routes) ──────────
        $router->addRoute('sitemap.xml', 'Sitemap:default');
        $router->addRoute('blog/feed.xml', 'Blog:feed');

        // ── Czech routes (default locale = cs) ───────────────────────────────
        $router->addRoute('/', ['presenter' => 'Homepage', 'action' => 'default', 'locale' => 'cs']);

        $router->addRoute('/blog', ['presenter' => 'Blog', 'action' => 'default', 'locale' => 'cs']);
        $router->addRoute('/blog/tag/<tag>', ['presenter' => 'Blog', 'action' => 'tag', 'locale' => 'cs']);
        $router->addRoute('/blog/<slug>', ['presenter' => 'Blog', 'action' => 'detail', 'locale' => 'cs']);

        $router->addRoute('/newsletter/subscribe', 'Newsletter:subscribe');
        $router->addRoute('/newsletter/confirm/<token>', 'Newsletter:confirm');
        $router->addRoute('/newsletter/unsubscribe/<token>', 'Newsletter:unsubscribe');

        $router->addRoute('/projekty', ['presenter' => 'Projects', 'action' => 'default', 'locale' => 'cs']);
        $router->addRoute('/projekty/<slug>', ['presenter' => 'Projects', 'action' => 'detail', 'locale' => 'cs']);

        $router->addRoute('/sluzby', ['presenter' => 'Services', 'action' => 'default', 'locale' => 'cs']);
        $router->addRoute('/ceny', ['presenter' => 'Pricing', 'action' => 'default', 'locale' => 'cs']);
        $router->addRoute('/o-mne', ['presenter' => 'About', 'action' => 'default', 'locale' => 'cs']);
        $router->addRoute('/kontakt', ['presenter' => 'Contact', 'action' => 'default', 'locale' => 'cs']);
        $router->addRoute('/soukromi', ['presenter' => 'Privacy', 'action' => 'default', 'locale' => 'cs']);

        $router->addRoute('/rezervace/sloty', 'Meeting:getSlots');
        $router->addRoute('/rezervace/uspech', ['presenter' => 'Meeting', 'action' => 'success', 'locale' => 'cs']);
        $router->addRoute('/rezervace', ['presenter' => 'Meeting', 'action' => 'default', 'locale' => 'cs']);

        // ── English routes (/en/ prefix, locale=en parameter) ─────────────────
        $router->addRoute('/en', ['presenter' => 'Homepage', 'action' => 'default', 'locale' => 'en']);
        $router->addRoute('/en/', ['presenter' => 'Homepage', 'action' => 'default', 'locale' => 'en']);

        $router->addRoute('/en/blog', ['presenter' => 'Blog', 'action' => 'default', 'locale' => 'en']);
        $router->addRoute('/en/blog/tag/<tag>', ['presenter' => 'Blog', 'action' => 'tag', 'locale' => 'en']);
        $router->addRoute('/en/blog/<slug>', ['presenter' => 'Blog', 'action' => 'detail', 'locale' => 'en']);

        $router->addRoute('/en/projects', ['presenter' => 'Projects', 'action' => 'default', 'locale' => 'en']);
        $router->addRoute('/en/projects/<slug>', ['presenter' => 'Projects', 'action' => 'detail', 'locale' => 'en']);

        $router->addRoute('/en/services', ['presenter' => 'Services', 'action' => 'default', 'locale' => 'en']);
        $router->addRoute('/en/pricing', ['presenter' => 'Pricing', 'action' => 'default', 'locale' => 'en']);
        $router->addRoute('/en/about', ['presenter' => 'About', 'action' => 'default', 'locale' => 'en']);
        $router->addRoute('/en/contact', ['presenter' => 'Contact', 'action' => 'default', 'locale' => 'en']);
        $router->addRoute('/en/privacy', ['presenter' => 'Privacy', 'action' => 'default', 'locale' => 'en']);

        $router->addRoute('/en/booking', ['presenter' => 'Meeting', 'action' => 'default', 'locale' => 'en']);
        $router->addRoute('/en/booking/success', ['presenter' => 'Meeting', 'action' => 'success', 'locale' => 'en']);

        return $router;
    }
}
