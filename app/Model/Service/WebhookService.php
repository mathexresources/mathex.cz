<?php

declare(strict_types=1);

namespace App\Model\Service;

use Nette\Database\Table\ActiveRow;

/**
 * Fires meeting events to a configured webhook URL (Zapier / Make.com).
 * Enable by setting 'webhook_url' in site_settings.
 */
final class WebhookService
{
    public function dispatch(ActiveRow $meeting, string $event, string $webhookUrl): void
    {
        if ($webhookUrl === '') {
            return;
        }

        $payload = json_encode([
            'event'          => $event,
            'meeting_id'     => $meeting->id,
            'name'           => $meeting->name,
            'email'          => $meeting->email,
            'phone'          => $meeting->phone ?? null,
            'company'        => $meeting->company ?? null,
            'service_type'   => $meeting->service_type ?? null,
            'preferred_date' => $meeting->preferred_date ?? null,
            'preferred_time' => $meeting->preferred_time ?? null,
            'status'         => $meeting->status,
            'message'        => $meeting->message ?? null,
            'created_at'     => (string) $meeting->created_at,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $ctx = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => "Content-Type: application/json\r\nUser-Agent: Mathex-Webhook/1.0\r\n",
                'content'       => $payload,
                'timeout'       => 5,
                'ignore_errors' => true,
            ],
        ]);

        @file_get_contents($webhookUrl, false, $ctx);
    }
}
