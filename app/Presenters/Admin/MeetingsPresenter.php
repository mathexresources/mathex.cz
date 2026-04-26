<?php

declare(strict_types=1);

namespace App\Presenters\Admin;

use App\Model\Database\BlockedDateRepository;
use App\Model\Database\MeetingRepository;
use App\Model\Service\CalendarExportService;
use App\Model\Service\EmailService;
use App\Model\Service\WebhookService;
use Nette\Application\UI\Form;

final class MeetingsPresenter extends BaseAdminPresenter
{
    public function __construct(
        private readonly MeetingRepository     $meetings,
        private readonly BlockedDateRepository $blockedDates,
        private readonly EmailService          $emailService,
        private readonly WebhookService        $webhookService,
        private readonly CalendarExportService $calendarExport,
    ) {
        parent::__construct();
    }

    public function renderDefault(string $status = ''): void
    {
        $this->template->pageTitle    = 'Schůzky';
        $this->template->statusFilter = $status;

        $conditions = [];
        if ($status !== '') {
            $conditions['status'] = $status;
        }

        $this->template->meetings = $conditions
            ? $this->meetings->findBy($conditions)->order('preferred_date DESC, preferred_time DESC')
            : $this->meetings->findAll()->order('preferred_date DESC, preferred_time DESC');

        $this->template->blockedDates   = $this->blockedDates->findActive()->fetchAll();
        $this->template->pendingCount   = $this->meetings->findPending()->count('*');
        $this->template->eventsUrl      = $this->link('eventsJson');
        $this->template->detailBase     = $this->link('detail', [0]);
        $this->template->statusBase     = $this->link('updateStatus', [0]);
    }

    /** FullCalendar JSON event feed. */
    public function actionEventsJson(): void
    {
        $start = (string) ($this->getParameter('start') ?? date('Y-m-01'));
        $end   = (string) ($this->getParameter('end')   ?? date('Y-m-t'));

        // Strip time component if FullCalendar sends ISO 8601
        $start = substr($start, 0, 10);
        $end   = substr($end, 0, 10);

        $events = $this->meetings->toFullCalendarEvents($start, $end);

        // Add blocked date ranges as background events
        foreach ($this->blockedDates->findActive() as $bd) {
            $endDate = date('Y-m-d', strtotime((string) $bd->date_to) + 86400); // FullCalendar end is exclusive
            $events[] = [
                'id'              => 'blocked-' . $bd->id,
                'title'           => $bd->reason ?: 'Blokováno',
                'start'           => $bd->date_from,
                'end'             => $endDate,
                'display'         => 'background',
                'backgroundColor' => 'rgba(239,68,68,0.18)',
                'borderColor'     => 'transparent',
                'classNames'      => ['fc-blocked'],
                'extendedProps'   => ['type' => 'blocked', 'blockedId' => $bd->id],
            ];
        }

        $this->sendJson($events);
    }

    public function renderDetail(int $id): void
    {
        $meeting = $this->meetings->find($id) ?? $this->error('Schůzka nenalezena.', 404);
        $this->template->pageTitle = 'Schůzka: ' . $meeting->name;
        $this->template->meeting   = $meeting;

        $this['notesForm']->setDefaults([
            'status'      => $meeting->status,
            'admin_notes' => $meeting->admin_notes,
        ]);
    }

    public function actionUpdateStatus(int $id): void
    {
        $meeting = $this->meetings->find($id) ?? $this->error('Schůzka nenalezena.', 404);

        $status = (string) $this->getParameter('status');
        $notes  = (string) $this->getParameter('notes', '');

        $allowedStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        if (!in_array($status, $allowedStatuses, true)) {
            $this->error('Neplatný stav.', 400);
        }

        $this->meetings->updateStatus($id, $status, $notes ?: null);

        if (in_array($status, ['confirmed', 'cancelled'], true)) {
            try {
                $updatedMeeting = $this->meetings->find($id);
                if ($updatedMeeting) {
                    if ($status === 'confirmed') {
                        $this->emailService->sendMeetingApproval($updatedMeeting);
                    } else {
                        $this->emailService->sendMeetingCancellation($updatedMeeting);
                    }
                }
            } catch (\Exception) {
            }

            try {
                $webhookUrl = $this->settings->get('webhook_url', '');
                if ($webhookUrl) {
                    $updatedMeeting ??= $this->meetings->find($id);
                    if ($updatedMeeting) {
                        $event = $status === 'confirmed' ? 'meeting.confirmed' : 'meeting.cancelled';
                        $this->webhookService->dispatch($updatedMeeting, $event, $webhookUrl);
                    }
                }
            } catch (\Throwable) {
            }
        }

        $label = match ($status) {
            'confirmed' => 'potvrzena',
            'cancelled' => 'zrušena',
            'completed' => 'dokončena',
            default     => 'aktualizována',
        };

        $this->flashMessage("Schůzka byla {$label}.", 'success');
        $this->redirect('detail', $id);
    }

    public function actionExportCsv(): void
    {
        $meetings = $this->meetings->findAll()->order('preferred_date DESC');

        $lines   = [];
        $lines[] = "\xEF\xBB\xBF" . 'ID,Jméno,E-mail,Telefon,Firma,Typ,Datum,Čas,Stav,Poznámky,Vytvořeno';

        foreach ($meetings as $m) {
            $lines[] = implode(',', array_map(
                fn($v) => '"' . str_replace('"', '""', (string) $v) . '"',
                [
                    $m->id, $m->name, $m->email, $m->phone ?? '',
                    $m->company ?? '', $m->service_type ?? '',
                    $m->preferred_date ?? '', $m->preferred_time ? date('H:i', strtotime((string) $m->preferred_time)) : '',
                    $m->status, $m->admin_notes ?? '',
                    $m->created_at,
                ],
            ));
        }

        $this->getHttpResponse()->setHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->getHttpResponse()->setHeader('Content-Disposition', 'attachment; filename="schusky.csv"');
        $this->sendResponse(new \Nette\Application\Responses\TextResponse(implode("\n", $lines)));
    }

    public function actionExportIcs(): void
    {
        $meetings = $this->meetings->findAll()->order('preferred_date ASC');
        $ics      = $this->calendarExport->generateIcs($meetings);

        $this->getHttpResponse()->setHeader('Content-Type', 'text/calendar; charset=utf-8');
        $this->getHttpResponse()->setHeader('Content-Disposition', 'attachment; filename="schusky.ics"');
        $this->sendResponse(new \Nette\Application\Responses\TextResponse($ics));
    }

    /** Signal: block a date range. */
    public function handleBlockDate(): void
    {
        $dateFrom = (string) $this->getParameter('date_from');
        $dateTo   = (string) ($this->getParameter('date_to') ?: $dateFrom);
        $reason   = (string) $this->getParameter('reason');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $this->flashMessage('Neplatný formát data.', 'error');
            $this->redirect('default');
        }

        if ($dateTo < $dateFrom) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $this->blockedDates->insert([
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'reason'    => $reason ?: null,
        ]);

        $label = $dateFrom === $dateTo ? $dateFrom : "{$dateFrom} – {$dateTo}";
        $this->flashMessage("Datum {$label} bylo zablokováno.", 'success');
        $this->redirect('default');
    }

    /** Signal: remove a blocked date. */
    public function handleUnblockDate(int $id): void
    {
        $this->blockedDates->delete($id);
        $this->flashMessage('Datum bylo odblokováno.', 'success');
        $this->redirect('default');
    }

    protected function createComponentNotesForm(): Form
    {
        $form = new Form();
        $form->addProtection();

        $form->addTextArea('admin_notes', 'Poznámky administrátora:')
            ->setHtmlAttribute('rows', 5);

        $form->addSelect('status', 'Stav:', [
            'pending'   => 'Čeká na potvrzení',
            'confirmed' => 'Potvrzeno',
            'cancelled' => 'Zrušeno',
            'completed' => 'Dokončeno',
        ]);

        $form->addSubmit('save', 'Uložit');
        $form->onSuccess[] = [$this, 'notesFormSucceeded'];

        return $form;
    }

    public function notesFormSucceeded(Form $form, \stdClass $values): void
    {
        $id      = (int) $this->getParameter('id');
        $meeting = $this->meetings->find($id) ?? $this->error('Schůzka nenalezena.', 404);

        $statusChanged = $meeting->status !== $values->status;
        $this->meetings->updateStatus($id, $values->status, $values->admin_notes ?: null);

        if ($statusChanged && in_array($values->status, ['confirmed', 'cancelled'], true)) {
            try {
                $updatedMeeting = $this->meetings->find($id);
                if ($updatedMeeting) {
                    if ($values->status === 'confirmed') {
                        $this->emailService->sendMeetingApproval($updatedMeeting);
                    } else {
                        $this->emailService->sendMeetingCancellation($updatedMeeting);
                    }
                }
            } catch (\Exception) {
            }

            try {
                $webhookUrl = $this->settings->get('webhook_url', '');
                if ($webhookUrl) {
                    $updatedMeeting ??= $this->meetings->find($id);
                    if ($updatedMeeting) {
                        $event = $values->status === 'confirmed' ? 'meeting.confirmed' : 'meeting.cancelled';
                        $this->webhookService->dispatch($updatedMeeting, $event, $webhookUrl);
                    }
                }
            } catch (\Throwable) {
            }
        }

        $this->flashMessage('Schůzka byla aktualizována.', 'success');
        $this->redirect('detail', $id);
    }
}
