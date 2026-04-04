<?php

namespace App\Services;

use App\Models\Setting;

class OpenAiTtsService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.openai.com/v1/audio/speech';
    private string $model = 'gpt-4o-mini-tts';
    private string $defaultVoice = 'coral';

    public function __construct()
    {
        $this->apiKey = trim((string)Setting::get('openai_api_key', AI_API_KEY));

        $configuredVoice = trim((string)Setting::get('openai_tts_voice', ''));
        if ($configuredVoice !== '') {
            $this->defaultVoice = $configuredVoice;
        }
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * TTS com streaming — chunks vão direto pro browser conforme são gerados.
     * Usa formato mp3 com streaming para menor latência percebida.
     * Retorna true se o stream iniciou com sucesso.
     */
    public function textToSpeechStream(string $text, ?string $voice = null, ?string $instructions = null): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $voice = $voice ?: $this->defaultVoice;

        $payload = [
            'model' => $this->model,
            'input' => $text,
            'voice' => $voice,
            'response_format' => 'mp3',
        ];

        if ($instructions !== null && $instructions !== '') {
            $payload['instructions'] = $instructions;
        }

        $headersSent = false;
        $gotData = false;

        $ch = curl_init($this->baseUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_WRITEFUNCTION => function ($ch, $data) use (&$headersSent, &$gotData) {
                $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($httpCode !== 200) {
                    return strlen($data);
                }
                if (!$headersSent) {
                    header('Content-Type: audio/mpeg');
                    header('Cache-Control: no-cache, no-store');
                    header('X-Accel-Buffering: no');
                    $headersSent = true;
                }
                echo $data;
                flush();
                $gotData = true;
                return strlen($data);
            },
        ]);

        curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200 && $gotData;
    }

    /**
     * TTS não-streaming (fallback). Retorna bytes MP3 ou null.
     */
    public function textToSpeech(string $text, ?string $voice = null, ?string $instructions = null): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $voice = $voice ?: $this->defaultVoice;

        $payload = [
            'model' => $this->model,
            'input' => $text,
            'voice' => $voice,
            'response_format' => 'mp3',
        ];

        if ($instructions !== null && $instructions !== '') {
            $payload['instructions'] = $instructions;
        }

        $ch = curl_init($this->baseUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode === 200 && $response) ? $response : null;
    }
}
