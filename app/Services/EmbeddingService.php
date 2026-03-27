<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Gera embeddings via OpenAI text-embedding-3-small.
 * Se a chave OpenAI não estiver configurada, retorna null silenciosamente
 * (o sistema degrada para busca por keywords sem interromper o fluxo).
 */
class EmbeddingService
{
    private const MODEL = 'text-embedding-3-small';
    private const DIMS  = 1536;

    /**
     * Gera embedding para um texto. Retorna array float[] ou null se indisponível.
     */
    public static function embed(string $text): ?array
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        $apiKey = trim((string)Setting::get('openai_api_key', defined('OPENAI_API_KEY') ? OPENAI_API_KEY : ''));
        if ($apiKey === '') {
            return null;
        }

        // Trunca para ~8000 chars (bem dentro do limite do modelo)
        if (mb_strlen($text, 'UTF-8') > 8000) {
            $text = mb_substr($text, 0, 8000, 'UTF-8');
        }

        try {
            $payload = json_encode([
                'model'      => self::MODEL,
                'input'      => $text,
                'dimensions' => self::DIMS,
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

            $ch = curl_init('https://api.openai.com/v1/embeddings');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey,
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || !is_string($response)) {
                return null;
            }

            $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            $vector = $data['data'][0]['embedding'] ?? null;

            if (!is_array($vector) || count($vector) !== self::DIMS) {
                return null;
            }

            return array_map('floatval', $vector);
        } catch (\Throwable $e) {
            error_log('[EmbeddingService] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calcula similaridade coseno entre dois vetores. Retorna float 0.0–1.0.
     */
    public static function cosineSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b) || empty($a)) {
            return 0.0;
        }

        $dot = 0.0;
        $magA = 0.0;
        $magB = 0.0;

        foreach ($a as $i => $v) {
            $bv = (float)($b[$i] ?? 0.0);
            $fv = (float)$v;
            $dot  += $fv * $bv;
            $magA += $fv * $fv;
            $magB += $bv * $bv;
        }

        $denom = sqrt($magA) * sqrt($magB);
        if ($denom < 1e-10) {
            return 0.0;
        }

        return (float)min(1.0, $dot / $denom);
    }

    /**
     * Serializa vetor para salvar em TEXT column.
     */
    public static function serializeVector(array $vector): string
    {
        return json_encode($vector, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desserializa vetor salvo em TEXT column.
     */
    public static function deserializeVector(string $json): ?array
    {
        if ($json === '' || $json[0] !== '[') {
            return null;
        }
        try {
            $v = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return is_array($v) ? array_map('floatval', $v) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
