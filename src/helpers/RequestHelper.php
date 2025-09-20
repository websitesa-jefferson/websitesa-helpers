<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Helper;

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
}
