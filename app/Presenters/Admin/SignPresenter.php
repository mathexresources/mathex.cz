<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Model\Database\UserRepository;
use App\Presenters\BasePresenter;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;

final class SignPresenter extends BasePresenter
{
    public function __construct(
        private readonly UserRepository $users,
    ) {
        parent::__construct();
    }

    public function startup(): void
    {
        parent::startup();
        $this->setLayout(__DIR__ . '/../../Templates/Admin/@layout.latte');
    }

    public function renderIn(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect(':Admin:Dashboard:');
        }

        // First-run setup: redirect to setup wizard if no admin exists
        if (!$this->users->hasAnyAdmin()) {
            $this->redirect(':Admin:Setup:');
        }

        $this->template->pageTitle = 'Přihlášení';
    }

    public function actionOut(): void
    {
        $this->getUser()->logout(true);
        $this->redirect('Homepage:');
    }

    protected function createComponentSignInForm(): Form
    {
        $form = new Form();
        $form->addProtection();

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte e-mail.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Zadejte heslo.');

        $form->addCheckbox('remember', 'Zapamatovat přihlášení na 30 dní');

        $form->addSubmit('login', 'Přihlásit se');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];

        return $form;
    }

    public function signInFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $this->getUser()->setExpiration($values->remember ? '30 days' : '2 hours');
            $this->getUser()->login($values->email, $values->password);
            $backlink = $this->getParameter('backlink');
            if (is_string($backlink)) {
                $this->restoreRequest($backlink);
            }
            $this->redirect(':Admin:Dashboard:');
        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }
}
