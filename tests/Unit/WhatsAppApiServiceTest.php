<?php

namespace Tests\Unit;

use App\Modules\Base\Domain\ApiService\WhatsAppApiService;
use App\Modules\Base\Domain\ApiService\WhatsAppBridgeException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WhatsAppApiServiceTest extends TestCase
{
    #[DataProvider('phoneNumberProvider')]
    public function test_phone_number_is_normalised_for_the_bridge(
        string $phone,
        ?string $countryCode,
        string $expected
    ): void {
        $this->assertSame($expected, WhatsAppApiService::normalisePhone($phone, $countryCode));
    }

    public static function phoneNumberProvider(): array
    {
        return [
            'local Egyptian number' => ['01012345678', '20', '201012345678'],
            'international plus prefix' => ['+20 10 1234 5678', '20', '201012345678'],
            'international double-zero prefix' => ['00201012345678', '20', '201012345678'],
        ];
    }

    public function test_message_is_sent_to_the_configured_session(): void
    {
        $this->configureBridge();
        Http::fake(['bridge.test/*' => Http::response(['id' => 'message-1'])]);

        $response = app(WhatsAppApiService::class)->sendMessage('01012345678', 'رسالة اختبار');

        $this->assertSame('message-1', $response['id']);
        Http::assertSent(fn (Request $request) => $request->url() === 'http://bridge.test/sessions/main/send'
            && $request->hasHeader('X-API-Key', 'test-key')
            && $request['to'] === '201012345678'
            && $request['message'] === 'رسالة اختبار');
    }

    public function test_permanent_bridge_rejection_is_exposed_to_the_queue_job(): void
    {
        $this->configureBridge();
        Http::fake(['bridge.test/*' => Http::response([
            'code' => 'recipient_opted_out',
            'error' => 'Recipient opted out',
        ], 400)]);

        try {
            app(WhatsAppApiService::class)->sendMessage('01012345678', 'رسالة اختبار');
            $this->fail('The bridge rejection was not raised.');
        } catch (WhatsAppBridgeException $exception) {
            $this->assertTrue($exception->permanent);
            $this->assertSame('recipient_opted_out', $exception->errorCode);
            $this->assertSame(400, $exception->status);
        }
    }

    private function configureBridge(): void
    {
        config()->set([
            'whatsapp.url' => 'http://bridge.test',
            'whatsapp.api_key' => 'test-key',
            'whatsapp.session' => 'main',
            'whatsapp.country_code' => '20',
        ]);
    }
}
