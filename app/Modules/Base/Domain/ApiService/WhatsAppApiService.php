<?php

namespace App\Modules\Base\Domain\ApiService;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppApiService
{
    private const PERMANENT_FAILURES = [
        'recipient_opted_out',
        'recipient_not_on_whatsapp',
        'invalid_recipient',
        'ambiguous_recipient',
        'duplicate_message',
        'message_too_long',
        'unauthorized',
    ];

    public function isConfigured(): bool
    {
        return filled(config('whatsapp.url'))
            && filled(config('whatsapp.api_key'))
            && filled(config('whatsapp.session'));
    }

    public function sendMessage(string $phone, string $text, ?string $countryCode = null): array
    {
        return $this->post('/send', [
            'to' => self::normalisePhone($phone, $countryCode),
            'message' => $text,
        ]);
    }

    public static function normalisePhone(string $phone, ?string $countryCode = null): string
    {
        $digits = self::digits($phone);
        $localCountryCode = self::digits(
            $countryCode ?? (string) config('whatsapp.country_code', '20')
        );

        if (str_starts_with($digits, '00')) {
            return substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            return $localCountryCode.ltrim($digits, '0');
        }

        return $digits;
    }

    private static function digits(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    private function client(): PendingRequest
    {
        return Http::withHeaders(['X-API-Key' => (string) config('whatsapp.api_key')])
            ->connectTimeout(10)
            ->timeout((int) config('whatsapp.timeout', 120))
            ->acceptJson()
            ->asJson();
    }

    private function post(string $path, array $payload): array
    {
        $response = $this->client()->post($this->url($path), $payload);

        if ($response->successful()) {
            return $response->json();
        }

        throw $this->rejection($response, $path);
    }

    private function rejection(Response $response, string $path): WhatsAppBridgeException
    {
        $errorCode = (string) $response->json('code', 'unknown_error');
        $details = $response->json('details');
        $details = is_array($details) ? $details : [];

        $this->logRejection($response, $path, $errorCode);

        return new WhatsAppBridgeException(
            $response->json('error') ?? 'WhatsApp bridge request failed',
            $errorCode,
            $response->status(),
            in_array($errorCode, self::PERMANENT_FAILURES, true),
            $this->retryAfterSeconds($response, $details),
        );
    }

    private function logRejection(Response $response, string $path, string $errorCode): void
    {
        Log::warning('WhatsApp bridge rejected a request', [
            'path' => $path,
            'status' => $response->status(),
            'code' => $errorCode,
            'request_id' => $response->json('requestId'),
        ]);
    }

    private function url(string $path): string
    {
        return rtrim((string) config('whatsapp.url'), '/')
            .'/sessions/'.config('whatsapp.session').$path;
    }

    private function retryAfterSeconds(Response $response, array $details): ?int
    {
        if (isset($details['retryAfterSeconds'])) {
            return (int) $details['retryAfterSeconds'];
        }

        $retryAfter = $response->header('Retry-After');

        return $retryAfter !== null ? (int) $retryAfter : null;
    }
}
