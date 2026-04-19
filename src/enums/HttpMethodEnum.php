<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Enums;

/**
 * Enum de métodos HTTP suportados.
 */
enum HttpMethodEnum: string
{
    case GET = 'GET';
    case HEAD = 'HEAD';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';
    case OPTIONS = 'OPTIONS';
    case CONNECT = 'CONNECT';
    case TRACE = 'TRACE';

    /**
     * Retorna todos os valores válidos como array de strings.
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Verifica se o método informado é válido.
     */
    public static function isValid(string $method): bool
    {
        return in_array(strtoupper($method), self::values(), true);
    }
}
