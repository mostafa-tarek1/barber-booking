<?php

namespace App\Modules\Notification\Application\Jobs;

use App\Modules\Base\Domain\ApiService\WhatsAppApiService;
use App\Modules\Base\Domain\ApiService\WhatsAppBridgeException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 180;

    public function __construct(
        public string $phone,
        public string $message,
    ) {}

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(WhatsAppApiService $whatsApp): void
    {
        try {
            $response = $whatsApp->sendMessage($this->phone, $this->message);

            Log::info('WhatsApp message sent', [
                'message_id' => $response['id'] ?? null,
            ]);
        } catch (WhatsAppBridgeException $exception) {
            $this->resolveBridgeFailure($exception);
        }
    }

    private function resolveBridgeFailure(WhatsAppBridgeException $exception): void
    {
        if ($exception->permanent) {
            Log::warning('WhatsApp message permanently rejected', [
                'code' => $exception->errorCode,
            ]);
            $this->fail($exception);

            return;
        }

        if ($exception->isQuotaExhausted()) {
            $this->release($exception->retryAfterSeconds ?? 3600);

            return;
        }

        throw $exception;
    }
}
