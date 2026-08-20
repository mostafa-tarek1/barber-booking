<?php

namespace App\Modules\Base\Domain\ApiService;

use RuntimeException;

class WhatsAppBridgeException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $errorCode,
        public readonly int $status,
        public readonly bool $permanent,
        public readonly ?int $retryAfterSeconds = null,
    ) {
        parent::__construct($message);
    }

    public function isQuotaExhausted(): bool
    {
        return in_array($this->errorCode, ['hourly_limit_reached', 'daily_limit_reached'], true);
    }
}
