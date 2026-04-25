<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Service\NewsletterService;
use Nette\Utils\Strings;

final class NewsletterPresenter extends BasePresenter
{
    public function __construct(
        private readonly NewsletterService $newsletterService,
    ) {
        parent::__construct();
    }

    public function actionSubscribe(): void
    {
        if (!$this->getRequest()->isMethod('POST')) {
            $this->redirect('Blog:default');
        }

        $email = Strings::trim((string) $this->getRequest()->getPost('email'));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashMessage('Zadejte platnou e-mailovou adresu.', 'error');
            $this->redirect('Blog:default');
        }

        try {
            $new = $this->newsletterService->subscribe($email);
            if ($new) {
                $this->flashMessage('Zkontrolujte svůj e-mail a potvrďte odběr.', 'success');
            } else {
                $this->flashMessage('Tento e-mail je již přihlášen k odběru.', 'info');
            }
        } catch (\Exception) {
            $this->flashMessage('Přihlášení se nezdařilo. Zkuste to prosím znovu.', 'error');
        }

        $this->redirect('Blog:default');
    }

    public function actionConfirm(string $token): void
    {
        if ($this->newsletterService->confirm($token)) {
            $this->flashMessage('Odběr newsletteru byl potvrzen. Vítejte!', 'success');
        } else {
            $this->flashMessage('Neplatný nebo expirovaný odkaz.', 'error');
        }
        $this->redirect('Blog:default');
    }

    public function actionUnsubscribe(string $token): void
    {
        if ($this->newsletterService->unsubscribe($token)) {
            $this->flashMessage('Byli jste odhlášeni z odběru newsletteru.', 'success');
        } else {
            $this->flashMessage('Neplatný odkaz pro odhlášení.', 'error');
        }
        $this->redirect('Blog:default');
    }
}
