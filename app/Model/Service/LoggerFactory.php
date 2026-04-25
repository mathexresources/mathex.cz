<?php

declare(strict_types=1);

namespace App\Model\Service;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

final class LoggerFactory
{
    /** @var array<string, LoggerInterface> */
    private array $loggers = [];

    public function __construct(
        private readonly string $logDir,
    ) {
    }

    public function get(string $channel = 'app'): LoggerInterface
    {
        if (!isset($this->loggers[$channel])) {
            $logger  = new Logger($channel);
            $handler = new RotatingFileHandler(
                "{$this->logDir}/{$channel}.log",
                maxFiles: 30,
                level: Logger::DEBUG,
            );
            $logger->pushHandler($handler);
            $this->loggers[$channel] = $logger;
        }
        return $this->loggers[$channel];
    }

    public function app(): LoggerInterface
    {
        return $this->get('app');
    }

    public function security(): LoggerInterface
    {
        return $this->get('security');
    }
}
