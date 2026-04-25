<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\ContactRepository;
use App\Model\Service\EmailService;
use App\Model\Service\RateLimiterService;
use Nette\Application\UI\Form;

final class ContactPresenter extends BasePresenter
{
    public function __construct(
        private readonly ContactRepository  $contact,
        private readonly EmailService       $emailService,
        private readonly RateLimiterService $rateLimiter,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->pageTitle = $this->translator->translate('contact.page_title');

        $this->metaDescription = $this->translator->translate('contact.meta_desc');
        $this->canonicalUrl    = $this->link('//Contact:default');
        $this->jsonLd = [
            '@context'   => 'https://schema.org',
            '@type'      => 'LocalBusiness',
            '@id'        => 'https://mathex.cz/#localbusiness',
            'name'       => 'Mathex.cz',
            'url'        => 'https://mathex.cz',
            'email'      => 'info@mathex.cz',
            'address'    => [
                '@type'           => 'PostalAddress',
                'addressLocality' => 'Brno',
                'addressCountry'  => 'CZ',
            ],
            'openingHours' => 'Mo-Fr 09:00-17:00',
            'sameAs'       => [
                'https://github.com/mathex',
                'https://linkedin.com/in/mathex',
            ],
        ];
    }

    protected function createComponentContactForm(): Form
    {
        $form = new Form();

        $form->addText('name', $this->translator->translate('contact.name'))
            ->setRequired($this->translator->translate('contact.name') . ' je povinné.')
            ->setMaxLength(255)
            ->setHtmlAttribute('placeholder', $this->translator->translate('contact.name_placeholder'));

        $form->addEmail('email', $this->translator->translate('contact.email'))
            ->setRequired($this->translator->translate('contact.email') . ' je povinné.')
            ->setMaxLength(255)
            ->setHtmlAttribute('placeholder', $this->translator->translate('contact.email_placeholder'));

        $form->addText('subject', $this->translator->translate('contact.subject'))
            ->setMaxLength(500)
            ->setHtmlAttribute('placeholder', $this->translator->translate('contact.subject_placeholder'));

        $form->addTextArea('message', $this->translator->translate('contact.message'))
            ->setRequired($this->translator->translate('contact.message') . ' je povinné.')
            ->setMaxLength(5000)
            ->setHtmlAttribute('rows', 6)
            ->setHtmlAttribute('placeholder', $this->translator->translate('contact.message_placeholder'));

        $form->addCheckbox('gdpr_consent', $this->translator->translate('contact.gdpr'))
            ->setRequired($this->translator->translate('contact.gdpr_required'));

        $form->addSubmit('send', $this->translator->translate('contact.send'));

        $form->onSuccess[] = [$this, 'handleSend'];

        return $form;
    }

    public function handleSend(Form $form, \stdClass $values): void
    {
        $ip = (string) ($this->getHttpRequest()->getRemoteAddress() ?? '');

        $rateLimitKey = 'contact:' . hash('sha256', $ip);
        if (!$this->rateLimiter->isAllowed($rateLimitKey, 5, 3600)) {
            $form->addError($this->translator->translate('contact.rate_limit'));
            return;
        }

        $this->contact->insert([
            'name'         => $values->name,
            'email'        => $values->email,
            'subject'      => $values->subject ?: null,
            'message'      => $values->message,
            'gdpr_consent' => (bool) $values->gdpr_consent,
        ]);

        try {
            $this->emailService->sendContactAdminNotification(
                senderName:  $values->name,
                senderEmail: $values->email,
                subject:     $values->subject ?: '(bez předmětu)',
                message:     $values->message,
            );
        } catch (\Throwable) {
            // Email failed – message is saved, continue
        }

        $this->flashMessage($this->translator->translate('contact.success'), 'success');
        $this->redirect('this');
    }

}
