<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Model\Database\NewsletterRepository;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Utils\Random;

final class NewsletterService
{
    public function __construct(
        private readonly NewsletterRepository $newsletter,
        private readonly IMailer $mailer,
        private readonly string $from,
        private readonly string $fromName,
        private readonly string $siteUrl,
        private readonly string $siteTitle,
    ) {
    }

    /**
     * Subscribes an email address. Sends confirmation email.
     * Returns false if the email is already confirmed.
     */
    public function subscribe(string $email): bool
    {
        $email    = mb_strtolower(trim($email));
        $existing = $this->newsletter->findByEmail($email);

        if ($existing && $existing->confirmed && !$existing->unsubscribed_at) {
            return false;
        }

        $token = Random::generate(48, '0-9a-zA-Z');

        if ($existing) {
            $this->newsletter->update((int) $existing->id, [
                'token'           => $token,
                'confirmed'       => false,
                'confirmed_at'    => null,
                'unsubscribed_at' => null,
            ]);
        } else {
            $this->newsletter->insert([
                'email'     => $email,
                'token'     => $token,
                'confirmed' => false,
            ]);
        }

        $this->sendConfirmationEmail($email, $token);
        return true;
    }

    public function confirm(string $token): bool
    {
        $row = $this->newsletter->findByToken($token);
        if (!$row || $row->confirmed) {
            return false;
        }
        $this->newsletter->confirm((int) $row->id);
        return true;
    }

    public function unsubscribe(string $token): bool
    {
        return $this->newsletter->unsubscribeByToken($token);
    }

    /**
     * Sends a newsletter to all confirmed subscribers.
     * $htmlBody may contain {{unsubscribe_url}} placeholder.
     */
    public function broadcast(string $subject, string $htmlBody): int
    {
        $subscribers = $this->newsletter->findConfirmed();
        $count       = 0;

        foreach ($subscribers as $sub) {
            $unsubUrl = rtrim($this->siteUrl, '/') . '/newsletter/unsubscribe/' . $sub->token;
            $body     = str_replace('{{unsubscribe_url}}', $unsubUrl, $htmlBody);

            $mail = $this->createMessage();
            $mail->addTo($sub->email);
            $mail->setSubject($subject);
            $mail->setHtmlBody($body);
            $this->mailer->send($mail);
            $count++;
        }

        return $count;
    }

    private function sendConfirmationEmail(string $email, string $token): void
    {
        $confirmUrl = rtrim($this->siteUrl, '/') . '/newsletter/confirm/' . $token;

        $body = '<!DOCTYPE html><html><body style="font-family:sans-serif;max-width:600px;margin:0 auto;padding:2rem;">'
            . '<h2>Potvrďte odběr newsletteru</h2>'
            . '<p>Děkujeme za zájem o newsletter <strong>' . htmlspecialchars($this->siteTitle) . '</strong>.</p>'
            . '<p>Pro dokončení registrace prosím klikněte na odkaz níže:</p>'
            . '<p><a href="' . $confirmUrl . '" style="background:#6366f1;color:#fff;padding:0.75rem 1.5rem;border-radius:6px;text-decoration:none;display:inline-block;">Potvrdit odběr</a></p>'
            . '<p style="color:#888;font-size:0.875rem;">Pokud jste se k odběru nepřihlásili, tento e-mail ignorujte.</p>'
            . '</body></html>';

        $mail = $this->createMessage();
        $mail->addTo($email);
        $mail->setSubject('Potvrďte odběr newsletteru – ' . $this->siteTitle);
        $mail->setHtmlBody($body);
        $this->mailer->send($mail);
    }

    private function createMessage(): Message
    {
        $msg = new Message();
        $msg->setFrom("{$this->fromName} <{$this->from}>");
        return $msg;
    }
}
