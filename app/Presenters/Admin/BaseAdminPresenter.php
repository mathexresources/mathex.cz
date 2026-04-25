<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Model\Database\ContactRepository;
use App\Presenters\BasePresenter;

abstract class BaseAdminPresenter extends BasePresenter
{
    private const IDLE_TIMEOUT = 7200; // 2 hours in seconds

    /** @inject */
    public ContactRepository $contactRepository;

    protected function startup(): void
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Admin:Sign:in', ['backlink' => $this->storeRequest()]);
        }

        if (!$this->getUser()->isInRole('admin')) {
            $this->error('Přístup odepřen.', 403);
        }

        $section = $this->getSession('admin_idle');
        $lastActive = (int) ($section->get('last_active') ?? 0);

        if ($lastActive > 0 && (time() - $lastActive) > self::IDLE_TIMEOUT) {
            $this->getUser()->logout(true);
            $this->redirect(':Admin:Sign:in');
        }

        $section->set('last_active', time());
    }

    protected function beforeRender(): void
    {
        parent::beforeRender();
        $this->template->unreadMessages = $this->contactRepository->countUnread();
        $this->setLayout(__DIR__ . '/../../Templates/Admin/@layout.latte');
    }
}
