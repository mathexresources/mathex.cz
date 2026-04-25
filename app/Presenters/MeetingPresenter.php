<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\BlockedDateRepository;
use App\Model\Database\MeetingRepository;
use App\Model\Database\ServiceRepository;
use App\Model\Database\SettingsRepository;
use App\Model\Service\EmailService;
use App\Model\Service\RateLimiterService;
use App\Model\Service\WebhookService;
use Nette\Application\UI\Form;

final class MeetingPresenter extends BasePresenter
{
    public function __construct(
        private readonly MeetingRepository     $meetings,
        private readonly ServiceRepository     $services,
        private readonly BlockedDateRepository $blockedDates,
        private readonly EmailService          $emailService,
        private readonly WebhookService        $webhookService,
        private readonly RateLimiterService    $rateLimiter,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->pageTitle = $this->translator->translate('meeting.page_title');
        $this->metaDescription     = $this->translator->translate('meeting.meta_desc');

        $services = [];
        foreach ($this->services->findActive() as $svc) {
            $services[$svc->slug] = (string) ($svc->{'title_' . $this->locale} ?? $svc->title_cs ?? $svc->title_en ?? $svc->slug);
        }
        $this->template->services          = $services;
        $this->template->bookingConfigJson = json_encode(
            [
                'slotsUrl'      => $this->link('Meeting:getSlots'),
                'blockedRanges' => $this->blockedDates->toCalendarArray(),
                'availableTimes' => $this->getAvailableTimes(),
            ],
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    public function renderSuccess(): void
    {
        $this->template->pageTitle = $this->translator->translate('meeting.success_title');
    }

    /** AJAX endpoint: returns available/booked slots for a given date. */
    public function actionGetSlots(): void
    {
        $date = $this->getParameter('date', '');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date)) {
            $this->sendJson(['error' => 'Invalid date format']);
        }

        $ts = strtotime((string) $date);
        if ($ts === false || $ts <= strtotime('today')) {
            $this->sendJson(['slots' => []]);
        }

        $dow = (int) date('N', $ts);
        if ($dow > 5) {
            $this->sendJson(['slots' => [], 'reason' => 'weekend']);
        }

        if ($this->blockedDates->isBlocked((string) $date)) {
            $this->sendJson(['slots' => [], 'reason' => 'blocked']);
        }

        $bookedSlots = $this->meetings->getBookedSlotsForDate((string) $date);
        $allSlots    = $this->getAvailableTimes();

        $result = [];
        foreach ($allSlots as $slot) {
            $result[] = [
                'time'      => $slot,
                'available' => !in_array($slot, $bookedSlots, true),
            ];
        }

        $this->sendJson(['slots' => $result]);
    }

    protected function createComponentBookingForm(): Form
    {
        $form = new Form();

        // Honeypot – bots fill this, humans don't
        $form->addText('hp_url', '')
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('tabindex', '-1')
            ->setHtmlAttribute('aria-hidden', 'true');

        $form->addText('name', 'Celé jméno:')
            ->setRequired('Zadejte své jméno.')
            ->setMaxLength(255);

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte e-mailovou adresu.')
            ->setMaxLength(255);

        $form->addText('phone', 'Telefon:')
            ->setMaxLength(50);

        $form->addText('company', 'Firma / web:')
            ->setMaxLength(255);

        $serviceOptions = [];
        foreach ($this->services->findActive() as $svc) {
            $serviceOptions[$svc->slug] = (string) ($svc->{'title_' . $this->locale} ?? $svc->title_cs ?? $svc->title_en ?? $svc->slug);
        }
        $form->addSelect('service_type', 'Typ služby:', $serviceOptions)
            ->setPrompt('Vyberte typ služby...')
            ->setRequired('Vyberte typ služby.');

        $minDate = $this->nextWeekday();
        $form->addText('preferred_date', 'Preferovaný datum:')
            ->setHtmlType('date')
            ->setHtmlAttribute('min', $minDate)
            ->setHtmlAttribute('data-booking-date', 'true');

        // Hidden field – JS populates it from slot buttons
        $form->addHidden('preferred_time');

        $form->addTextArea('message', 'Stručný popis projektu:')
            ->setMaxLength(2000)
            ->setHtmlAttribute('rows', 5);

        $form->addSubmit('send', 'Rezervovat call');

        $form->onSuccess[] = [$this, 'handleBook'];

        return $form;
    }

    public function handleBook(Form $form, \stdClass $values): void
    {
        // Honeypot check – silently succeed without saving
        if (!empty($values->hp_url)) {
            $this->redirect('success');
        }

        $ip = (string) ($this->getHttpRequest()->getRemoteAddress() ?? '');
        if (!$this->rateLimiter->isAllowed('meeting:' . hash('sha256', $ip), 5, 3600)) {
            $form->addError('Příliš mnoho požadavků. Zkuste to prosím za hodinu.');
            return;
        }

        // Weekend / past date guard
        $date = $values->preferred_date ?: null;
        if ($date) {
            $ts  = strtotime($date);
            $dow = (int) date('N', $ts ?: 0);
            if (!$ts || $ts <= strtotime('today') || $dow > 5) {
                $form['preferred_date']->addError('Vyberte pracovní den v budoucnosti.');
                return;
            }
            if ($this->blockedDates->isBlocked($date)) {
                $form['preferred_date']->addError('Tento den není k dispozici. Vyberte prosím jiný termín.');
                return;
            }
        }

        $data = [
            'name'           => $values->name,
            'email'          => $values->email,
            'phone'          => $values->phone ?: null,
            'company'        => $values->company ?: null,
            'service_type'   => $values->service_type,
            'preferred_date' => $date,
            'preferred_time' => $values->preferred_time ?: null,
            'message'        => $values->message ?: null,
            'status'         => 'pending',
            'ip_address'     => $this->getHttpRequest()->getRemoteAddress(),
        ];

        $meeting = $this->meetings->insert($data);

        if ($meeting && $meeting !== true) {
            try {
                $this->emailService->sendMeetingPendingConfirmation($meeting);
                $this->emailService->sendMeetingNotificationToAdmin($meeting);
            } catch (\Throwable) {
            }

            try {
                $webhookUrl = $this->settings->get('webhook_url', '');
                if ($webhookUrl) {
                    $this->webhookService->dispatch($meeting, 'meeting.created', $webhookUrl);
                }
            } catch (\Throwable) {
            }
        }

        $this->redirect('success');
    }

    /** @return list<string> */
    private function getAvailableTimes(): array
    {
        $raw = $this->settings->get('meeting_available_times', '09:00,10:00,11:00,13:00,14:00,15:00,16:00');
        return array_values(array_filter(
            array_map('trim', explode(',', (string) $raw)),
            fn($s) => $s !== '',
        ));
    }

    private function nextWeekday(): string
    {
        $ts = strtotime('+1 day');
        while (date('N', $ts) > 5) {
            $ts = strtotime('+1 day', $ts);
        }
        return date('Y-m-d', $ts);
    }
}
