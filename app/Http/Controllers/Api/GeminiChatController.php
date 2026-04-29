<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Product;

class GeminiChatController extends Controller
{
    /**
     * Handle the chat request with Gemini AI.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array',
        ]);

        $userMessage = $request->input('message');
        $history = $request->input('history', []); // Previous messages if any

        // Fetch active products to inject as context
        $products = Product::where('active', true)->get();

        $productContext = "Here is the current list of products available at BakerDan:\n";
        foreach ($products as $product) {
            $productContext .= "- {$product->name} (Category: {$product->category}): {$product->description}. Price: {$product->price} PHP.\n";
        }

        // Prepare the system instruction
        $systemInstruction = "You are a helpful and friendly AI assistant for BakerDan, a bakery. " .
            "Your job is to answer customer questions about our products, help them choose, and provide information. " .
            "Be polite, enthusiastic, and concise. " .
            "Do not answer questions completely unrelated to the bakery or general knowledge if it strays too far from baking/food. " .
            "talk less and be specific or straight to the point, don't be too wordy or verbose. for example don't say 'Oh wow our walnut brownies are simply divine, crafted with the finest cocoa and loaded with premium walnuts, offering a delightful crunch with every bite!' instead just say 'Walnut Brownies: Rich, fudgy, and packed with crunchy walnuts. A customer favorite!'" .
            $productContext;

        // Format history for Gemini API
        // Gemini expects: {"contents": [{"role": "user", "parts": [{"text": "..."}]}, {"role": "model", "parts": [{"text": "..."}]}]}
        $contents = [];

        // We can prepend the system instruction to the very first message or as a system_instruction field (supported in newer Gemini versions)
        // Let's use the system_instruction field for gemini-1.5-flash

        foreach ($history as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $msg['text']]],
            ];
        }

        // Add the current user message
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userMessage]],
        ];

        $apiKey = env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Gemini API key is not configured.'
            ], 500);
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}";

        try {
            $response = Http::post($url, [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $systemInstruction]
                    ]
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 1000,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I could not generate a response.';

                return response()->json([
                    'success' => true,
                    'reply' => $reply,
                ]);
            } else {
                \Illuminate\Support\Facades\Log::error('Gemini API Error: ', $response->json());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to Gemini AI.',
                    'error' => $response->json()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
