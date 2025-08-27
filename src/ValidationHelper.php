<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers;

use Yii;
use yii\helpers\IpHelper;

class ValidationHelper
{
    /**
     * Verifica se o IP do usuário é válido.
     *
     * @param list<non-empty-string> $ips Lista de regras de IP (ex.: "10.0.0.*", "192.168.1.1", "10.0.0.0/24", "*")
     */
    public static function matchIP(array $ips): bool
    {
        $userIP = Yii::$app->request->getUserIP();

        // Se não conseguimos obter o IP, bloqueia por segurança
        if ($userIP === null || $userIP === '') {
            return false;
        }

        // A partir daqui, $userIP é string não vazia para o PHPStan
        /** @var non-empty-string $userIP */

        // Se a lista está vazia, libera todos os IPs
        if ($ips === []) {
            return true;
        }

        foreach ($ips as $ipRule) {
            // Permite todos
            if ($ipRule === '*') {
                return true;
            }

            // Match exato
            if ($ipRule === $userIP) {
                return true;
            }

            // Prefixo com wildcard (*)
            if (str_contains($ipRule, '*')) {
                $pos = strpos($ipRule, '*');
                if ($pos !== false && strncmp($userIP, $ipRule, $pos) === 0) {
                    return true;
                }
            }

            // Faixa de IP (CIDR)
            if (str_contains($ipRule, '/') && IpHelper::inRange($userIP, $ipRule)) {
                return true;
            }
        }

        return false;
    }

    public static function validateDateTime(string $date, string $format = 'Y-m-d H:i:s'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d !== false && $d->format($format) === $date;
    }
}
