<?php

declare(strict_types=1);

namespace App\Presenters\Error;

use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\Responses\ForwardResponse;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use Tracy\ILogger;

final class Error4xxPresenter extends Presenter
{
    public function __construct(
        private readonly ILogger $logger,
    ) {
        parent::__construct();
    }

    public function startup(): void
    {
        parent::startup();
        if (!$this->getRequest()->isMethod(Request::FORWARD)) {
            $this->error();
        }
    }

    public function renderDefault(\Throwable $exception): void
    {
        if ($exception instanceof BadRequestException) {
            $code = $exception->getHttpCode();
            $this->template->title   = match ($code) {
                403     => '403 Forbidden',
                404     => '404 Stránka nenalezena',
                405     => '405 Method Not Allowed',
                default => "{$code} Chyba",
            };
            $this->template->code    = $code;
            $this->template->message = $exception->getMessage();
            $this->getHttpResponse()->setCode($code);
        } else {
            $this->logger->log($exception, ILogger::EXCEPTION);
            $this->template->title   = '500 Interní chyba serveru';
            $this->template->code    = 500;
            $this->template->message = 'Omlouváme se, došlo k neočekávané chybě.';
            $this->getHttpResponse()->setCode(IResponse::S500_InternalServerError);
        }
    }
}
