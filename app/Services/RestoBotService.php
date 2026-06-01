<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class RestoBotService
{
    public function answer(string $question): string
    {
        if (! $this->isRecipeQuestion($question)) {
            return __('I can only help restaurant admins create new dishes, recipes, ingredients, and menu descriptions.');
        }

        $apiKey = (string) config('services.groq.key');

        if ($apiKey === '') {
            return __('Groq API key is not configured yet. Add GROQ_API_KEY in .env to enable RestoBot.');
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(25)
                ->acceptJson()
                ->post(rtrim((string) config('services.groq.base_url'), '/').'/chat/completions', [
                    'model' => config('services.groq.model'),
                    'temperature' => 0.55,
                    'max_tokens' => 850,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => implode(' ', [
                                'You are RestoBot inside RestoSmart.',
                                'Only help restaurant admins create new restaurant dishes, recipes, ingredients, preparation steps, menu descriptions, pricing ideas, plating, allergens, and kitchen notes.',
                                'If the user asks anything unrelated to recipes or adding dishes, refuse briefly.',
                                'Return practical, concise output with sections: Dish idea, Ingredients, Preparation, Menu description, Kitchen notes, Allergens.',
                            ]),
                        ],
                        ['role' => 'user', 'content' => $question],
                    ],
                ]);

            if (! $response->successful()) {
                return __('RestoBot could not reach Groq right now. Please check the API key and try again.');
            }

            return trim((string) data_get($response->json(), 'choices.0.message.content'))
                ?: __('RestoBot did not return a recipe. Try a more specific dish idea.');
        } catch (Throwable $exception) {
            report($exception);

            return __('RestoBot could not answer right now. Please try again.');
        }
    }

    private function isRecipeQuestion(string $question): bool
    {
        $question = mb_strtolower($question);

        $keywords = [
            'recipe', 'dish', 'menu', 'ingredient', 'cook', 'kitchen', 'prep', 'sauce', 'dessert', 'starter', 'main course',
            'recette', 'plat', 'ingrédient', 'ingredient', 'cuisine', 'préparation', 'sauce', 'dessert',
            'wasfa', 'wصفة', 'وصفة', 'طبق', 'مقادير', 'مكونات', 'طهي', 'مطبخ',
            'ma9adir', 'makla', 'tajine', 'tagine', 'tacos', 'pizza', 'burger', 'salad', 'salade', 'sandwich', 'pasta',
        ];

        foreach ($keywords as $keyword) {
            if (str_contains($question, mb_strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }
}
