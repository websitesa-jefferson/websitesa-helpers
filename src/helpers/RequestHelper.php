<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Helpers;

use Yii;
use yii\web\Request;

final class RequestHelper
{
    /**
     * Obtém o nome do host da requisição atual.
     */
    public static function getHostName(): string
    {
        // 1) Tente HTTP_HOST (caso de aplicações web atrás de proxy/reverso)
        if (isset($_SERVER['HTTP_HOST']) && is_string($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== '') {
            return $_SERVER['HTTP_HOST'];
        }

        // 2) Fallback: SERVER_NAME (servidor web)
        if (isset($_SERVER['SERVER_NAME']) && is_string($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] !== '') {
            return $_SERVER['SERVER_NAME'];
        }

        // 3) Fallback final: hostname do SO (CLI, jobs, etc.)
        $h = gethostname();
        return is_string($h) ? $h : '';
    }

    // /** Contexto web */
    // public static function getHostName(): string
    // {
    //     if (Yii::$app instanceof Application) {
    //         // yii\web\Request::getHostName() já normaliza e retorna string
    //         return Yii::$app->request->getHostName();
    //     }

    //     $h = gethostname();
    //     return is_string($h) ? $h : '';
    // }

    /** Retorna o token do cabeçalho */
    public static function getTokenHeader(): string
    {
        $request = Yii::$app->get('request', false);
        if ($request instanceof Request === false) {
            return '';
        }

        $authHeader = $request->getHeaders()->get('Authorization');
        if (is_string($authHeader) === false) {
            return '';
        }

        return str_starts_with($authHeader, 'Bearer ')
            ? substr($authHeader, 7)
            : $authHeader;
    }

    /** Retorna o domínio de origem da requisição */
    public static function getRequestOrigin(): ?string
    {
        $request = Yii::$app->get('request', false);
        if ($request instanceof Request === false) {
            return null;
        }

        $origin = $request->headers->get('Origin');
        if (is_string($origin) === false || $origin === '') {
            $origin = $request->headers->get('Referer');
        }
        if (is_string($origin) === false || $origin === '') {
            $origin = $request->hostInfo;
        }

        if (is_string($origin) === false || $origin === '') {
            return null;
        }

        $url = $origin;
        if (!str_contains($url, '://')) {
            $url = 'https://' . $url;
        }

        $parsed = parse_url($url);
        if ($parsed === false || !isset($parsed['host'])) {
            return null;
        }

        $host = $parsed['host'];
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';

        return 'https://' . $host . $port;
    }
}
