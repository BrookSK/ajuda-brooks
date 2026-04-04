<?php

namespace App\Services;

use App\Models\Setting;

class ElevenLabsService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.elevenlabs.io/v1';
    private string $defaultVoiceId;

    public function __construct()
    {
        $this->apiKey = trim((string)Setting::get('elevenlabs_api_key', ''));
        $this->defaultVoiceId = trim((string)Setting::get('elevenlabs_voice_id', 'EXAVITQu4vr4xnSDxMaL'));
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * TTS não-streaming (fallback). Retorna bytes MP3 ou null.
     */
    public function textToSpeech(string $text, ?string $voiceId = null): ?string
    {
        if (!$this->isConfigured()) return null;

        $voice = $voiceId ?: $this->defaultVoiceId;
        $url = $this->baseUrl . '/text-to-speech/' . urlencode($voice);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'text' => $text,
                'model_id' => 'eleven_flash_v2_5',
                'voice_settings' => [
                    'stability' => 0.5,
                    'similarity_boost' => 0.75,
                ],
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: audio/mpeg',
                'Content-Type: application/json',
                'xi-api-key: ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode === 200 && $response) ? $response : null;
    }

    /**
     * TTS com HTTP Streaming — passthrough de chunks pro browser.
     * Usa modelo Flash v2.5 (~75ms latência) + output_format pcm pra menor latência.
     * Retorna true se stream iniciou com sucesso.
     */
    public function textToSpeechStream(string $text, ?string $voiceId = null): bool
    {
        if (!$this->isConfigured()) return false;

        $voice = $voiceId ?: $this->defaultVoiceId;
        $url = $this->baseUrl . '/text-to-speech/' . urlencode($voice) . '/stream'
             . '?output_format=mp3_22050_32';

        $payload = json_encode([
            'text' => $text,
            'model_id' => 'eleven_flash_v2_5',
            'voice_settings' => [
                'stability' => 0.5,
                'similarity_boost' => 0.75,
            ],
        ]);

        $headersSent = false;
        $gotData = false;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HTTPHEADER => [
                'Accept: audio/mpeg',
                'Content-Type: application/json',
                'xi-api-key: ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_WRITEFUNCTION => function ($ch, $data) use (&$headersSent, &$gotData) {
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200 && $gotData;
    }

    /**
     * Lista as vozes disponíveis.
     */
    public function listVoices(): array
    {
        if (!$this->isConfigured()) return [];

        $ch = curl_init($this->baseUrl . '/voices');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['xi-api-key: ' . $this->apiKey],
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) return [];
        $data = json_decode($response, true);
        return $data['voices'] ?? [];
    }
}
