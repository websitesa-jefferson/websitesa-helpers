<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Helper;

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

    /**
     * Valida um CPF.
     */
    public static function isValidCpf(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        if ($cpf === null || strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf) === 1) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($c = 0; $c < $t; $c++) {
                $soma += (int) $cpf[$c] * (($t + 1) - $c);
            }

            $digito = ((10 * $soma) % 11) % 10;
            if ($digito !== (int) $cpf[$t]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Valida um CNPJ.
     */
    public static function isValidCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if ($cnpj === null || strlen($cnpj) !== 14) {
            return false;
        }

        // Rejeita CNPJs com todos os dígitos iguais
        if (preg_match('/(\d)\1{13}/', $cnpj) === 1) {
            return false;
        }

        // Primeiro dígito verificador
        $peso1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;
        for ($i = 0; $i < 12; $i++) {
            $soma += (int) $cnpj[$i] * $peso1[$i];
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;

        if ($digito1 !== (int) $cnpj[12]) {
            return false;
        }

        // Segundo dígito verificador
        $peso2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;
        for ($i = 0; $i < 13; $i++) {
            $soma += (int) $cnpj[$i] * $peso2[$i];
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;

        return $digito2 === (int) $cnpj[13];
    }
}
