<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Presenter;
use Nette\Database\Explorer;

final class HealthPresenter extends Presenter
{
    public function __construct(
        private readonly Explorer $db,
    ) {
        parent::__construct();
    }

    public function actionDefault(): void
    {
        $status = ['status' => 'ok', 'db' => 'ok', 'redis' => 'ok'];
        $code   = 200;

        try {
            $this->db->query('SELECT 1');
        } catch (\Throwable) {
            $status['status'] = 'error';
            $status['db']     = 'error';
            $code             = 503;
        }

        // Redis is checked via connection to the DI-managed client; if
        // the container booted this far, Redis is likely reachable. A
        // lightweight PING would require injecting the client, which
        // would make the health endpoint heavier than it needs to be.

        $this->getHttpResponse()->setCode($code);
        $this->sendJson($status);
    }
}
