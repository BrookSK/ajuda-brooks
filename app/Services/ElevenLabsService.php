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
        $this->defaultVoiceId = trim((string)Setting::get('elevenlabs_voice_id', 'EXAVITQu4vr4xnSDxMaL')); // Sarah default
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Converte texto em áudio usando a API da ElevenLabs.
     * Retorna o conteúdo binário do MP3 ou null em caso de erro.
     */
    public function textToSpeech(string $text, ?string $voiceId = null): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $voice = $voiceId ?: $this->defaultVoiceId;
        $url = $this->baseUrl . '/text-to-speech/' . urlencode($voice);

        $payload = json_encode([
            'text' => $text,
            'model_id' => 'eleven_multilingual_v2',
            'voice_settings' => [
                'stability' => 0.5,
                'similarity_boost' => 0.75,
                'style' => 0.0,
                'use_speaker_boost' => true,
            ],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
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

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        return $response;
    }

    /**
     * Converte texto em áudio usando a API da ElevenLabs com streaming.
     * Faz flush dos chunks direto pro output (browser recebe enquanto gera).
     * Retorna true se conseguiu iniciar o stream, false caso contrário.
     */
    public function textToSpeechStream(string $text, ?string $voiceId = null): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $voice = $voiceId ?: $this->defaultVoiceId;
        // Endpoint de streaming da ElevenLabs
        $url = $this->baseUrl . '/text-to-speech/' . urlencode($voice) . '/stream';

        $payload = json_encode([
            'text' => $text,
            'model_id' => 'eleven_multilingual_v2',
            'voice_settings' => [
                'stability' => 0.5,
                'similarity_boost' => 0.75,
                'style' => 0.0,
                'use_speaker_boost' => true,
            ],
        ]);

        // Desabilita output buffering pra streaming real
        while (ob_get_level()) {
            ob_end_flush();
        }

        header('Content-Type: audio/mpeg');
        header('Transfer-Encoding: chunked');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no'); // nginx

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
            CURLOPT_WRITEFUNCTION => function ($ch, $data) {
                echo $data;
                if (function_exists('flush')) {
                    flush();
                }
                return strlen($data);
            },
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    /**
     * Lista as vozes disponíveis na conta.
     */
    public function listVoices(): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $ch = curl_init($this->baseUrl . '/voices');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'xi-api-key: ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return [];
        }

        $data = json_decode($response, true);
        return $data['voices'] ?? [];
    }
}
