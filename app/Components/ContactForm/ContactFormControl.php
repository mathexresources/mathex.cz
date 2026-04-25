<?php

declare(strict_types=1);

namespace App\Components\ContactForm;

use App\Model\Database\MessageRepository;
use App\Model\Mail\MailSender;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

final class ContactFormControl extends Control
{
    public function __construct(
        private readonly MessageRepository $messages,
        private readonly MailSender $mailSender,
        private readonly string $notifyEmail,
    ) {
    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/ContactFormControl.latte');
        $this->template->render();
    }

    protected function createComponentForm(): Form
    {
        $form = new Form();

        $form->addText('name', 'Jméno:')
            ->setRequired('Zadejte své jméno.')
            ->setMaxLength(255);

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte svůj e-mail.')
            ->setMaxLength(255);

        $form->addText('subject', 'Předmět:')
            ->setMaxLength(500);

        $form->addTextArea('message', 'Zpráva:')
            ->setRequired('Napište zprávu.')
            ->setMaxLength(5000)
            ->setHtmlAttribute('rows', 6);

        $form->addSubmit('send', 'Odeslat zprávu');

        $form->onSuccess[] = [$this, 'formSucceeded'];

        return $form;
    }

    public function formSucceeded(Form $form, \stdClass $values): void
    {
        $this->messages->insert([
            'name'       => $values->name,
            'email'      => $values->email,
            'subject'    => $values->subject,
            'message'    => $values->message,
            'ip_address' => $this->getPresenter()->getHttpRequest()->getRemoteAddress(),
        ]);

        try {
            $this->mailSender->sendContactNotification(
                toEmail: $this->notifyEmail,
                senderName: $values->name,
                senderEmail: $values->email,
                subject: $values->subject ?: '(bez předmětu)',
                body: $values->message,
            );
        } catch (\Throwable) {
            // Notification failed, message is saved – continue silently
        }

        $this->getPresenter()->flashMessage('Zpráva byla odeslána. Ozvu se co nejdříve.', 'success');
        $this->getPresenter()->redirect('this');
    }
}
