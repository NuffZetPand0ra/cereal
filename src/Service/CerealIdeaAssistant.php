<?php

namespace App\Service;

class CerealIdeaAssistant
{
    /**
     * @return array<string, mixed>
     */
    public function suggestDraft(string $name, string $idea): array
    {
        $providedName = trim($name);
        $idea = trim($idea);
        $generatedName = $providedName !== '' ? $providedName : $this->suggestNameFromIdea($idea);

        $fallback = $this->heuristicSuggestion($generatedName, $idea);

        $apiKey = trim((string) (getenv('OPENAI_API_KEY') ?: ($_ENV['OPENAI_API_KEY'] ?? $_SERVER['OPENAI_API_KEY'] ?? '')));
        if ($apiKey === '') {
            return $this->withSuggestedName($fallback, $providedName, $generatedName);
        }

        $llmSuggestion = $this->openAiSuggestion($providedName, $idea, $apiKey);
        if ($llmSuggestion === null) {
            return $this->withSuggestedName($fallback, $providedName, $generatedName);
        }

        $resolvedName = $providedName;
        if ($resolvedName === '') {
            $resolvedName = trim((string) ($llmSuggestion['suggestedName'] ?? ''));
            if ($resolvedName === '') {
                $resolvedName = $generatedName;
            }
        }

        $result = [
            'source' => 'openai',
            'nutrients' => $this->sanitizeNutrients($llmSuggestion['nutrients'] ?? []),
            'image' => $this->buildImageSuggestion($resolvedName, $idea, (string) ($llmSuggestion['imagePrompt'] ?? ''), $apiKey),
            'notes' => (string) ($llmSuggestion['notes'] ?? ''),
        ];

        return $this->withSuggestedName($result, $providedName, $resolvedName);
    }

    /**
     * @return array<string, mixed>
     */
    private function heuristicSuggestion(string $name, string $idea): array
    {
        $text = strtolower($name.' '.$idea);

        $nutrients = [
            'calories' => 110,
            'protein' => 3,
            'fat' => 1,
            'sodium' => 180,
            'fiber' => 2.0,
            'carbo' => 23.0,
            'sugars' => 9,
            'potass' => 80,
            'vitamins' => 25,
            'shelf' => 1,
            'weight' => 1.0,
            'cups' => 0.75,
        ];

        if ($this->containsAny($text, ['protein', 'gym', 'athlete', 'muscle'])) {
            $nutrients['protein'] = 10;
            $nutrients['sugars'] = 5;
            $nutrients['carbo'] = 18.0;
            $nutrients['fiber'] = 5.0;
            $nutrients['calories'] = 130;
        }

        if ($this->containsAny($text, ['kids', 'sweet', 'dessert', 'chocolate', 'candy', 'fun'])) {
            $nutrients['sugars'] = max($nutrients['sugars'], 13);
            $nutrients['carbo'] = max($nutrients['carbo'], 26.0);
            $nutrients['calories'] = max($nutrients['calories'], 140);
        }

        if ($this->containsAny($text, ['healthy', 'light', 'diet', 'low sugar', 'clean'])) {
            $nutrients['sugars'] = min($nutrients['sugars'], 4);
            $nutrients['fiber'] = max($nutrients['fiber'], 6.0);
            $nutrients['sodium'] = min($nutrients['sodium'], 120);
            $nutrients['calories'] = min($nutrients['calories'], 100);
        }

        if ($this->containsAny($text, ['fruit', 'berry', 'banana', 'apple', 'tropical'])) {
            $nutrients['potass'] = max($nutrients['potass'], 150);
        }

        $imagePrompt = sprintf(
            'studio product shot of cereal box "%s", cereal bowl in front, visual theme: %s, bright commercial food photography, high detail',
            $name,
            $idea
        );

        return [
            'source' => 'heuristic',
            'nutrients' => $this->sanitizeNutrients($nutrients),
            'image' => $this->buildImageSuggestion($name, $idea, $imagePrompt),
            'notes' => 'Heuristic draft generated. Add OPENAI_API_KEY to enable LLM suggestions.',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function openAiSuggestion(string $name, string $idea, string $apiKey): ?array
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $prompt = [
            'You are helping create a cereal product draft.',
            'Return strict JSON only with keys: nutrients, imagePrompt, notes, suggestedName.',
            'nutrients must include numeric fields: calories, protein, fat, sodium, fiber, carbo, sugars, potass, vitamins, shelf, weight, cups.',
            'Use realistic per-serving values, all non-negative.',
            $name === '' ? 'Cereal name: (not provided)' : sprintf('Cereal name: %s', $name),
            sprintf('Idea: %s', $idea),
            'If cereal name is not provided, suggest a short marketable name in suggestedName. Otherwise set suggestedName to an empty string.',
        ];

        $payload = [
            'model' => 'gpt-4o-mini',
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => 'You are a food product formulation assistant.'],
                ['role' => 'user', 'content' => implode("\n", $prompt)],
            ],
            'temperature' => 0.7,
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer '.$apiKey,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if (!is_string($response) || $status < 200 || $status >= 300) {
            return null;
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return null;
        }

        $content = $decoded['choices'][0]['message']['content'] ?? null;
        if (!is_string($content) || $content === '') {
            return null;
        }

        $json = json_decode($content, true);
        return is_array($json) ? $json : null;
    }

    /**
     * @param array<string, mixed> $nutrients
     * @return array<string, int|float|null>
     */
    private function sanitizeNutrients(array $nutrients): array
    {
        return [
            'calories' => $this->sanitizeInt($nutrients['calories'] ?? 110),
            'protein' => $this->sanitizeInt($nutrients['protein'] ?? 3),
            'fat' => $this->sanitizeInt($nutrients['fat'] ?? 1),
            'sodium' => $this->sanitizeInt($nutrients['sodium'] ?? 180),
            'fiber' => $this->sanitizeFloat($nutrients['fiber'] ?? 2.0),
            'carbo' => $this->sanitizeFloat($nutrients['carbo'] ?? 23.0),
            'sugars' => $this->sanitizeInt($nutrients['sugars'] ?? 9),
            'potass' => $this->sanitizeInt($nutrients['potass'] ?? 80),
            'vitamins' => $this->sanitizeInt($nutrients['vitamins'] ?? 25),
            'shelf' => $this->sanitizeInt($nutrients['shelf'] ?? 1),
            'weight' => $this->sanitizeFloat($nutrients['weight'] ?? 1.0),
            'cups' => $this->sanitizeFloat($nutrients['cups'] ?? 0.75),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buildImageSuggestion(string $name, string $idea, string $prompt, ?string $apiKey = null): array
    {
        $cleanPrompt = trim($prompt);
        if ($cleanPrompt === '') {
            $cleanPrompt = sprintf(
                'packaging concept for cereal "%s" inspired by %s, colorful, modern, commercial product render',
                $name,
                $idea
            );
        }

        if ($apiKey !== null && $apiKey !== '') {
            $openAiImageUrl = $this->openAiImageSuggestion($cleanPrompt, $apiKey);
            if ($openAiImageUrl !== null) {
                return [
                    'prompt' => $cleanPrompt,
                    'url' => $openAiImageUrl,
                ];
            }
        }

        $seed = abs(crc32(strtolower($name.'|'.$idea)));
        $url = 'https://image.pollinations.ai/prompt/'.rawurlencode($cleanPrompt).'?width=768&height=768&seed='.$seed;

        return [
            'prompt' => $cleanPrompt,
            'url' => $url,
        ];
    }

    private function openAiImageSuggestion(string $prompt, string $apiKey): ?string
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $payload = [
            'model' => 'gpt-image-1',
            'prompt' => $prompt,
            'size' => '1024x1024',
            'quality' => 'low',
        ];

        $ch = curl_init('https://api.openai.com/v1/images/generations');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer '.$apiKey,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if (!is_string($response) || $status < 200 || $status >= 300) {
            return null;
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return null;
        }

        $url = $decoded['data'][0]['url'] ?? null;

        return is_string($url) && $url !== '' ? $url : null;
    }

    private function sanitizeInt(mixed $value): int
    {
        return max(0, (int) round((float) $value));
    }

    private function sanitizeFloat(mixed $value): float
    {
        return max(0.0, round((float) $value, 2));
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function withSuggestedName(array $result, string $providedName, string $suggestedName): array
    {
        if ($providedName !== '') {
            return $result;
        }

        $result['suggestedName'] = $suggestedName;

        return $result;
    }

    private function suggestNameFromIdea(string $idea): string
    {
        $normalized = preg_replace('/[^a-z0-9]+/i', ' ', strtolower($idea));
        $normalized = is_string($normalized) ? trim($normalized) : '';

        if ($normalized === '') {
            return 'Morning Crunch';
        }

        $words = array_values(array_filter(explode(' ', $normalized), static function (string $word): bool {
            return strlen($word) >= 3 && !in_array($word, ['with', 'from', 'for', 'that', 'this', 'your', 'and', 'the'], true);
        }));

        $first = $words[0] ?? 'Morning';
        $second = $words[1] ?? 'Crunch';

        return ucfirst($first).' '.ucfirst($second);
    }

    /**
     * @param array<int, string> $needles
     */
    private function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }
}
