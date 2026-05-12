<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeminiChatController extends Controller
{
    /**
     * Handle the chat request with Gemini AI.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array|max:20',
            'history.*.role' => 'required_with:history|string|in:user,model,assistant',
            'history.*.text' => 'required_with:history|string|max:4000',
        ]);

        $userMessage = $request->input('message');
        $history = collect($request->input('history', []))->take(-12)->values();

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('product_name')
            ->limit(80)
            ->get([
                'category',
                'description',
                'flavors_available',
                'price',
                'price_label',
                'product_name',
                'sizes_available',
            ]);

        $productContext = $this->productContext($products);

        $systemInstruction = "You are a helpful and friendly AI assistant for BakerDan, a bakery. " .
            "Your job is to answer customer questions about our products, help them choose, and provide information. " .
            "Be polite, enthusiastic, and concise. " .
            "Do not answer questions completely unrelated to the bakery or general knowledge if it strays too far from baking/food. " .
            "Talk less and be specific or straight to the point. " .
            $productContext;

        $contents = [];

        foreach ($history as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $msg['text']]],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userMessage]],
        ];

        $apiKey = trim((string) config('services.gemini.api_key'));

        if ($apiKey === '') {
            return response()->json([
                'success' => false,
                'message' => 'Gemini API key is not configured.',
            ], 500);
        }

        $model = trim((string) config('services.gemini.model', 'gemini-2.5-flash'));
        $baseUrl = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $url = $baseUrl . '/' . $this->modelPath($model) . ':generateContent';

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout((int) config('services.gemini.timeout', 20))
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                ])
                ->post($url, [
                    'system_instruction' => [
                        'parts' => [
                            ['text' => $systemInstruction],
                        ],
                    ],
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 1000,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = data_get($data, 'candidates.0.content.parts.0.text');

                if (blank($reply)) {
                    Log::warning('Gemini API returned no text response.', [
                        'finish_reason' => data_get($data, 'candidates.0.finishReason'),
                        'model' => $model,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Gemini did not return a text response.',
                    ], 502);
                }

                return response()->json([
                    'success' => true,
                    'reply' => $reply,
                ]);
            }

            Log::error('Gemini API error.', [
                'body' => $response->json(),
                'model' => $model,
                'status' => $response->status(),
            ]);

            return response()->json(array_filter([
                'success' => false,
                'message' => 'Failed to connect to Gemini AI.',
                'error' => config('app.debug') ? $response->json() : null,
            ], fn ($value) => $value !== null), 502);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function modelPath(string $model): string
    {
        $model = $model !== '' ? $model : 'gemini-2.5-flash';

        if (Str::startsWith($model, 'models/')) {
            return $model;
        }

        return 'models/' . $model;
    }

    private function productContext($products): string
    {
        if ($products->isEmpty()) {
            return "No active products are currently listed in BakerDan's catalog.";
        }

        $lines = $products->map(function (Product $product): string {
            $details = collect([
                $product->description,
                $product->sizes_available ? 'Sizes: ' . $product->sizes_available : null,
                $product->flavors_available ? 'Flavors: ' . $product->flavors_available : null,
            ])->filter()->implode(' ');

            $price = $product->price_label ?: ('PHP ' . number_format((float) $product->price, 2));

            return sprintf(
                '- %s (Category: %s, Price: %s): %s',
                $product->product_name,
                $product->category,
                $price,
                $details ?: 'No description provided.'
            );
        })->implode("\n");

        return "Current active products at BakerDan:\n{$lines}";
    }
}
