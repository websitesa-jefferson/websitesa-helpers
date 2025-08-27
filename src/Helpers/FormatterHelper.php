<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers;

use DateTime;
use yii\base\InvalidValueException;
use yii\i18n\Formatter as BaseFormatter;

class FormatterHelper extends BaseFormatter
{
    /**
     * Calcula a diferença em minutos entre duas datas no formato Y-m-d H:i:s
     *
     * @param string $start Data inicial no formato Y-m-d H:i:s
     * @param string $end Data final no formato Y-m-d H:i:s
     * @return int Total de minutos entre as datas
     * @throws InvalidValueException Se as datas forem inválidas
     */
    public static function timeInMinutes(string $start, string $end): int
    {
        $startDate = DateTime::createFromFormat('Y-m-d H:i:s', $start);
        $endDate = DateTime::createFromFormat('Y-m-d H:i:s', $end);

        if ($startDate === false || $endDate === false) {
            throw new InvalidValueException('Datas inválidas fornecidas.');
        }

        // diferença absoluta em segundos
        $seconds = abs($endDate->getTimestamp() - $startDate->getTimestamp());

        // converte para minutos com arredondamento de >=30s para cima
        $minutes = intdiv($seconds, 60);
        if ($seconds % 60 >= 30) {
            $minutes++;
        }

        return $minutes;
    }

    /**
     * Formata CPF no padrão 000.000.000-00
     *
     * @param string $cpf CPF contendo somente números
     * @return string CPF formatado
     * @throws InvalidValueException Se CPF não tiver 11 dígitos ou regex falhar
     */
    public static function asCpf(string $cpf): string
    {
        $onlyDigits = preg_replace('/\D/', '', $cpf);
        if ($onlyDigits === null) {
            throw new InvalidValueException('Falha ao processar o CPF.');
        }

        if (strlen($onlyDigits) !== 11) {
            throw new InvalidValueException('CPF deve conter 11 caracteres numéricos.');
        }

        $formatted = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $onlyDigits);
        if ($formatted === null) {
            throw new InvalidValueException('Falha ao formatar o CPF.');
        }

        return $formatted;
    }

    /**
     * Retorna a data no formato desejado ou timestamp Unix
     *
     * @param string $date Data em qualquer formato válido (ex: 'dd/mm/yyyy' ou 'yyyy-mm-dd')
     * @param string $format Formato para saída (padrão 'Y-m-d')
     * @param bool $returnTimestamp Se true, retorna timestamp (int), senão string formatada
     * @throws InvalidValueException Se a data for inválida ou vazia
     */
    public static function asDate_(
        string $date,
        string $format = 'Y-m-d',
        bool $returnTimestamp = false
    ): string|int {
        if (trim($date) === '') {
            throw new InvalidValueException('Data não pode ficar em branco.');
        }

        $normalizedDate = str_replace('/', '-', $date);
        $timestamp = strtotime($normalizedDate);

        if ($timestamp === false) {
            throw new InvalidValueException('Data inválida fornecida.');
        }

        return $returnTimestamp ? $timestamp : date($format, $timestamp);
    }

    /**
     * Remove tudo que não for número da string.
     *
     * @param mixed $txt Texto de entrada
     * @return string Texto contendo só números (string vazia se não houver dígitos)
     */
    public static function asInteger_(mixed $txt): string
    {
        $result = filter_var($txt, FILTER_SANITIZE_NUMBER_INT);

        // filter_var pode retornar false em erro; garanta string
        if ($result === false) {
            return '';
        }

        return $result;
    }

    /**
     * Converte segundos para formato HH:mm:ss
     *
     * @param int $segundos Quantidade de segundos
     * @return string Tempo formatado como HH:mm:ss
     */
    public static function secondsToTime(int $segundos): string
    {
        $horas = intdiv($segundos, 3600);
        $minutos = intdiv($segundos % 3600, 60);
        $segundos = $segundos % 60;

        return sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
    }
}
