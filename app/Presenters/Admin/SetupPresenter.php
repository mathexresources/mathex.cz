<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Model\Database\UserRepository;
use App\Presenters\BasePresenter;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;

final class SetupPresenter extends BasePresenter
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly Passwords $passwords,
    ) {
        parent::__construct();
    }

    public function startup(): void
    {
        parent::startup();
        $this->setLayout(__DIR__ . '/../../Templates/Admin/@layout.latte');

        // Setup wizard is only for first run
        if ($this->users->hasAnyAdmin()) {
            $this->redirect(':Admin:Sign:in');
        }
    }

    public function renderDefault(): void
    {
        $this->template->pageTitle = 'Nastavení administrátorského účtu';
    }

    protected function createComponentSetupForm(): Form
    {
        $form = new Form();

        $form->addText('name', 'Celé jméno:')
            ->setRequired('Zadejte jméno.')
            ->setMaxLength(255);

        $form->addEmail('email', 'E-mail (login):')
            ->setRequired('Zadejte e-mail.')
            ->setMaxLength(255);

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Zadejte heslo.')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků.', 8);

        $form->addPassword('password_confirm', 'Potvrzení hesla:')
            ->setRequired('Potvrďte heslo.')
            ->addRule(Form::EQUAL, 'Hesla se neshodují.', $form['password']);

        $form->addSubmit('create', 'Vytvořit administrátora');

        $form->onSuccess[] = [$this, 'setupFormSucceeded'];

        return $form;
    }

    public function setupFormSucceeded(Form $form, \stdClass $values): void
    {
        // Double-check no admin was created meanwhile
        if ($this->users->hasAnyAdmin()) {
            $this->redirect(':Admin:Sign:in');
        }

        $this->users->insert([
            'name'          => $values->name,
            'email'         => $values->email,
            'password_hash' => $this->passwords->hash($values->password),
            'role'          => 'admin',
            'is_active'     => true,
        ]);

        $this->flashMessage('Administrátorský účet byl vytvořen. Nyní se přihlaste.', 'success');
        $this->redirect(':Admin:Sign:in');
    }
}
