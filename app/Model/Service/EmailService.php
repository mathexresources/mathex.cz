<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Model\Mail\MailTemplateRenderer;
use Nette\Database\Table\ActiveRow;
use Nette\Mail\IMailer;
use Nette\Mail\Message;

final class EmailService
{
    public function __construct(
        private readonly IMailer $mailer,
        private readonly string $from,
        private readonly string $fromName,
        private readonly string $adminEmail,
        private readonly string $adminPanelUrl,
        private readonly string $siteUrl,
        private readonly MailTemplateRenderer $mailRenderer,
    ) {
    }

    /** Sent immediately when a booking is submitted (status=pending). */
    public function sendMeetingPendingConfirmation(ActiveRow $meeting): void
    {
        $html = $this->mailRenderer->render('MeetingClientPending.latte', [
            'meeting'   => $meeting,
            'siteTitle' => $this->fromName,
            'fromEmail' => $this->from,
        ]);

        $mail = $this->createMessage();
        $mail->addTo($meeting->email, $meeting->name);
        $mail->setSubject('Vaše rezervace byla přijata – ' . $this->fromName);
        $mail->setHtmlBody($html);
        $this->mailer->send($mail);
    }

    /** Sent when admin confirms the meeting (status=confirmed). */
    public function sendMeetingApproval(ActiveRow $meeting): void
    {
        $html = $this->mailRenderer->render('MeetingClientApproved.latte', [
            'meeting'   => $meeting,
            'siteTitle' => $this->fromName,
            'fromEmail' => $this->from,
        ]);

        $mail = $this->createMessage();
        $mail->addTo($meeting->email, $meeting->name);
        $mail->setSubject('Schůzka potvrzena – ' . $this->fromName);
        $mail->setHtmlBody($html);
        $this->mailer->send($mail);
    }

    /** Sent when admin cancels the meeting (status=cancelled). */
    public function sendMeetingCancellation(ActiveRow $meeting): void
    {
        $html = $this->mailRenderer->render('MeetingClientCancelled.latte', [
            'meeting'    => $meeting,
            'siteTitle'  => $this->fromName,
            'fromEmail'  => $this->from,
            'bookingUrl' => rtrim($this->siteUrl, '/') . '/rezervace',
        ]);

        $mail = $this->createMessage();
        $mail->addTo($meeting->email, $meeting->name);
        $mail->setSubject('Schůzka zrušena – ' . $this->fromName);
        $mail->setHtmlBody($html);
        $this->mailer->send($mail);
    }

    /** Sent to the admin when a new booking arrives. */
    public function sendMeetingNotificationToAdmin(ActiveRow $meeting): void
    {
        $detailUrl = rtrim($this->adminPanelUrl, '/') . '/admin/meetings/detail/' . $meeting->id;

        $html = $this->mailRenderer->render('MeetingAdminNotification.latte', [
            'meeting'   => $meeting,
            'siteTitle' => $this->fromName,
            'adminUrl'  => $detailUrl,
        ]);

        $mail = $this->createMessage();
        $mail->addTo($this->adminEmail);
        $mail->setReplyTo($meeting->email, $meeting->name);
        $mail->setSubject('Nová žádost o schůzku – ' . $meeting->name);
        $mail->setHtmlBody($html);
        $this->mailer->send($mail);
    }

    /** Backward-compat alias – kept so existing callers still compile. */
    public function sendMeetingConfirmation(ActiveRow $meeting): void
    {
        match ($meeting->status) {
            'confirmed' => $this->sendMeetingApproval($meeting),
            'cancelled' => $this->sendMeetingCancellation($meeting),
            default     => $this->sendMeetingPendingConfirmation($meeting),
        };
    }

    public function sendContactReply(string $toEmail, string $toName, string $subject, string $body): void
    {
        $mail = $this->createMessage();
        $mail->addTo($toEmail, $toName);
        $mail->setSubject($subject);
        $mail->setHtmlBody(nl2br(htmlspecialchars($body, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')));
        $this->mailer->send($mail);
    }

    public function sendContactAdminNotification(string $senderName, string $senderEmail, string $subject, string $message): void
    {
        $mail = $this->createMessage();
        $mail->addTo($this->adminEmail);
        $mail->setReplyTo($senderEmail, $senderName);
        $mail->setSubject('Nová zpráva z kontaktního formuláře: ' . $subject);
        $mail->setHtmlBody(
            '<p><strong>Odesílatel:</strong> ' . htmlspecialchars($senderName) . ' &lt;' . htmlspecialchars($senderEmail) . '&gt;</p>'
            . '<p><strong>Předmět:</strong> ' . htmlspecialchars($subject) . '</p>'
            . '<hr>'
            . '<p>' . nl2br(htmlspecialchars($message)) . '</p>',
        );
        $this->mailer->send($mail);
    }

    private function createMessage(): Message
    {
        $mail = new Message();
        $mail->setFrom("{$this->fromName} <{$this->from}>");
        return $mail;
    }
}
