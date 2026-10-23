<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Helpers;

use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

final class UuidHelper
{
    private const UUID_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
    private const UUID_V7_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    /**
     * Gera um UUIDv7 em conformidade com a RFC 9562.
     *
     * @param int|null $timestampMs Timestamp opcional em milissegundos (padrão: agora).
     */
    public static function generateV7(?int $timestampMs = null): string
    {
        $timeMs = $timestampMs ?? (int) (microtime(true) * 1000);
        if ($timeMs < 0 || $timeMs > 0xFFFFFFFFFFFF) {
            throw new InvalidArgumentException('Timestamp em milissegundos deve estar entre 0 e 281474976710655 (48 bits).');
        }

        // 6 bytes para o timestamp de 48 bits (big-endian)
        $timeBytes = pack('J', $timeMs);
        $timeBytes = substr($timeBytes, 2, 6);

        // 10 bytes aleatórios criptograficamente seguros
        $randomBytes = random_bytes(10);

        // Byte 6: 4 bits de versão (0111 = 0x70) | 4 bits aleatórios
        $byte6 = chr((ord($randomBytes[0]) & 0x0f) | 0x70);
        $byte7 = $randomBytes[1];

        // Byte 8: 2 bits de variante RFC 9562 (10xx xxxx = 0x80) | 6 bits aleatórios
        $byte8 = chr((ord($randomBytes[2]) & 0x3f) | 0x80);
        $remainingRandom = substr($randomBytes, 3); // 7 bytes restantes (bytes 9..15)

        $binary = $timeBytes . $byte6 . $byte7 . $byte8 . $remainingRandom;
        $hex = bin2hex($binary);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    /**
     * Valida se a string é um formato UUID genérico válido.
     */
    public static function isValid(string $uuid): bool
    {
        return preg_match(self::UUID_REGEX, $uuid) === 1;
    }

    /**
     * Valida se a string é especificamente um UUIDv7 válido (RFC 9562).
     */
    public static function isV7(string $uuid): bool
    {
        return preg_match(self::UUID_V7_REGEX, $uuid) === 1;
    }

    /**
     * Extrai o timestamp Unix em milissegundos a partir de um UUIDv7.
     */
    public static function getTimestampMs(string $uuid): int
    {
        if (!self::isV7($uuid)) {
            throw new InvalidArgumentException('UUID fornecido não é um UUIDv7 válido.');
        }

        $clean = str_replace('-', '', $uuid);
        $timeHex = substr($clean, 0, 12);

        return (int) hexdec($timeHex);
    }

    /**
     * Converte o timestamp contido no UUIDv7 em um objeto DateTimeImmutable.
     */
    public static function getDateTime(string $uuid): DateTimeImmutable
    {
        $timestampMs = self::getTimestampMs($uuid);
        $seconds = (int) ($timestampMs / 1000);
        $microseconds = ($timestampMs % 1000) * 1000;

        $dateTime = DateTimeImmutable::createFromFormat('U.u', sprintf('%d.%06d', $seconds, $microseconds));
        if ($dateTime === false) {
            throw new RuntimeException('Falha ao instanciar DateTimeImmutable a partir do UUID.');
        }

        return $dateTime;
    }
}
