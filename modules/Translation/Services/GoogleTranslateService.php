<?php

namespace Modules\Translation\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleTranslateService
{
    /**
     * Google Translate API Key.
     *
     * @var string|null
     */
    protected $apiKey;

    /**
     * Create a new service instance.
     */
    public function __construct()
    {
        $this->apiKey = config('services.google.translate_api_key') ?: env('GOOGLE_TRANSLATE_API_KEY');
    }

    /**
     * Translate text from source language to target language.
     *
     * @param string $text
     * @param string $source
     * @param string $target
     * @param string $format 'text' or 'html'
     * @return string|null
     */
    public function translate(string $text, string $source, string $target, string $format = 'text'): ?string
    {
        if (empty($this->apiKey)) {
            Log::warning('Google Translate API key is not configured.');
            return null;
        }

        if ($source === $target) {
            return $text;
        }

        try {
            $response = Http::post("https://translation.googleapis.com/language/translate/v2?key={$this->apiKey}", [
                'q' => [$text],
                'target' => $target,
                'source' => $source,
                'format' => $format,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data']['translations'][0]['translatedText'] ?? null;
            }

            Log::error('Google Translate API error response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'source' => $source,
                'target' => $target,
            ]);
        } catch (\Exception $e) {
            Log::error('Google Translate API exception: ' . $e->getMessage(), [
                'exception' => $e,
                'source' => $source,
                'target' => $target,
            ]);
        }

        return null;
    }

    /**
     * Translate multiple texts from source language to target language.
     *
     * @param array $texts
     * @param string $source
     * @param string $target
     * @param array|string $formats Array of formats corresponding to each text ('text' or 'html') or a single format string.
     * @return array|null Array of translated strings, or null on failure.
     */
    public function translateBatch(array $texts, string $source, string $target, $formats = 'text'): ?array
    {
        if (empty($this->apiKey)) {
            Log::warning('Google Translate API key is not configured.');
            return null;
        }

        if ($source === $target) {
            return $texts;
        }

        if (empty($texts)) {
            return [];
        }

        try {
            // Determine the format. If any input has HTML format, we use 'html' for the entire request
            // to ensure Google Translate correctly parses tags.
            $format = 'text';
            if (is_array($formats)) {
                if (in_array('html', $formats)) {
                    $format = 'html';
                }
            } elseif ($formats === 'html') {
                $format = 'html';
            }

            $response = Http::post("https://translation.googleapis.com/language/translate/v2?key={$this->apiKey}", [
                'q' => $texts,
                'target' => $target,
                'source' => $source,
                'format' => $format,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $translations = [];
                foreach ($data['data']['translations'] as $t) {
                    $translations[] = $t['translatedText'] ?? '';
                }
                return $translations;
            }

            Log::error('Google Translate API error response (batch)', [
                'status' => $response->status(),
                'body' => $response->body(),
                'source' => $source,
                'target' => $target,
            ]);
        } catch (\Exception $e) {
            Log::error('Google Translate API exception (batch): ' . $e->getMessage(), [
                'exception' => $e,
                'source' => $source,
                'target' => $target,
            ]);
        }

        return null;
    }
}
