<?php

declare(strict_types=1);

namespace App\Components\ContactForm;

use App\Model\Database\MessageRepository;
use App\Model\Mail\MailSender;

final class ContactFormFactory
{
    public function __construct(
        private readonly MessageRepository $messages,
        private readonly MailSender $mailSender,
        private readonly string $contactEmail,
    ) {
    }

    public function create(): ContactFormControl
    {
        return new ContactFormControl($this->messages, $this->mailSender, $this->contactEmail);
    }
}
