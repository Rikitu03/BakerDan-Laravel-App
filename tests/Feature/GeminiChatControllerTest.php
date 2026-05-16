<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiChatControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
    }

    public function test_chat_uses_configured_gemini_service_and_returns_reply(): void
    {
        config([
            'services.gemini.api_key' => 'test-gemini-key',
            'services.gemini.base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'services.gemini.model' => 'gemini-2.5-flash',
            'services.gemini.timeout' => 20,
        ]);

        Product::query()->create([
            'category' => 'Bread',
            'product_name' => 'Classic Sourdough',
            'description' => 'Naturally leavened loaf.',
            'price' => 500,
            'price_label' => 'PHP 500',
            'is_active' => true,
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Classic Sourdough is a good pick.'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $response = $this->postJson('/api/chat', [
            'message' => 'What bread should I get?',
            'history' => [
                ['role' => 'user', 'text' => 'Hi'],
                ['role' => 'model', 'text' => 'Hello!'],
            ],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('reply', 'Classic Sourdough is a good pick.');

        Http::assertSent(function ($request): bool {
            $body = $request->data();

            return str_contains((string) $request->url(), '/models/gemini-2.5-flash:generateContent')
                && $request->hasHeader('x-goog-api-key', 'test-gemini-key')
                && isset($body['system_instruction'])
                && data_get($body, 'contents.2.parts.0.text') === 'What bread should I get?'
                && str_contains((string) data_get($body, 'system_instruction.parts.0.text'), 'Classic Sourdough');
        });
    }

    public function test_chat_reports_missing_gemini_api_key(): void
    {
        config([
            'services.gemini.api_key' => null,
        ]);

        $this->postJson('/api/chat', [
            'message' => 'Can you help?',
        ])
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Gemini API key is not configured.');
    }
}
