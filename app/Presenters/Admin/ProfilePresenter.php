<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Model\Database\UserRepository;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;

final class ProfilePresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly Passwords $passwords,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->pageTitle = 'Můj profil';
        $user = $this->users->find($this->getUser()->getId());
        $this->template->adminUser = $user;
    }

    protected function createComponentPasswordForm(): Form
    {
        $form = new Form();
        $form->addProtection();

        $form->addPassword('current_password', 'Současné heslo:')
            ->setRequired('Zadejte současné heslo.');

        $form->addPassword('new_password', 'Nové heslo:')
            ->setRequired('Zadejte nové heslo.')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků.', 8);

        $form->addPassword('new_password_confirm', 'Potvrzení nového hesla:')
            ->setRequired('Potvrďte nové heslo.')
            ->addRule(Form::EQUAL, 'Hesla se neshodují.', $form['new_password']);

        $form->addSubmit('save', 'Změnit heslo');

        $form->onSuccess[] = [$this, 'passwordFormSucceeded'];

        return $form;
    }

    public function passwordFormSucceeded(Form $form, \stdClass $values): void
    {
        $userId = $this->getUser()->getId();
        $user   = $this->users->find($userId);

        if (!$user || !$this->passwords->verify($values->current_password, (string) $user->password_hash)) {
            $form->addError('Současné heslo je nesprávné.');
            return;
        }

        $this->users->updatePassword($userId, $this->passwords->hash($values->new_password));
        $this->flashMessage('Heslo bylo změněno.', 'success');
        $this->redirect('this');
    }
}
