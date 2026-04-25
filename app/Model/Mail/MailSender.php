<?php

declare(strict_types=1);

namespace App\Model\Mail;

use Nette\Mail\IMailer;
use Nette\Mail\Message;

final class MailSender
{
    public function __construct(
        private readonly IMailer $mailer,
        private readonly string $from,
        private readonly string $fromName,
    ) {
    }

    public function sendContactNotification(
        string $toEmail,
        string $senderName,
        string $senderEmail,
        string $subject,
        string $body,
    ): void {
        $mail = new Message();
        $mail->setFrom("{$this->fromName} <{$this->from}>");
        $mail->addTo($toEmail);
        $mail->setReplyTo($senderEmail, $senderName);
        $mail->setSubject("Nová zpráva: {$subject}");
        $mail->setHtmlBody(
            "<p><strong>Od:</strong> {$senderName} &lt;{$senderEmail}&gt;</p>"
            . "<p><strong>Předmět:</strong> {$subject}</p>"
            . "<hr><p>" . nl2br(htmlspecialchars($body)) . "</p>",
        );

        $this->mailer->send($mail);
    }
}
