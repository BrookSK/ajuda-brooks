<?php

namespace App\Services;

/**
 * Dispara requisições HTTP sem aguardar resposta (fire-and-forget).
 * Usa fastcgi_finish_request() se disponível (PHP-FPM), caso contrário cURL com timeout mínimo.
 */
class AsyncHttpService
{
    /**
     * Dispara uma GET request sem bloquear o processo atual.
     */
    public static function fireAndForget(string $url): void
    {
        if ($url === '') {
            return;
        }

        try {
            $ch = curl_init($url);
            if ($ch === false) {
                return;
            }
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_TIMEOUT        => 1,      // timeout de 1s — não bloqueia
                CURLOPT_CONNECTTIMEOUT => 1,
                CURLOPT_NOSIGNAL       => 1,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER     => ['Connection: close'],
            ]);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable $e) {
            // Silencia — é fire-and-forget, não crítico
            error_log('[AsyncHttpService] fireAndForget falhou: ' . $e->getMessage());
        }
    }

    /**
     * Registra callback para executar após o response ser enviado ao cliente
     * (usando fastcgi_finish_request se disponível no PHP-FPM).
     */
    public static function afterResponse(callable $callback): void
    {
        if (function_exists('fastcgi_finish_request')) {
            // PHP-FPM: processa depois de enviar o response
            register_shutdown_function(function () use ($callback) {
                fastcgi_finish_request();
                try {
                    $callback();
                } catch (\Throwable $e) {
                    error_log('[AsyncHttpService] afterResponse error: ' . $e->getMessage());
                }
            });
        } else {
            // Outros SAPIs: executa normalmente (com latência mínima, pois é callback leve)
            register_shutdown_function(function () use ($callback) {
                try {
                    $callback();
                } catch (\Throwable $e) {
                    error_log('[AsyncHttpService] afterResponse error: ' . $e->getMessage());
                }
            });
        }
    }
}
