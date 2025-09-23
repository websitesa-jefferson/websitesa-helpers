<?php

namespace Websitesa\Yii2\Helpers\Helper;

use yii\helpers\Console;

class ConsoleHelper
{
    /**
     * Mensagem de erro em vermelho (stderr)
     */
    public static function error(string $message): void
    {
        fwrite(STDERR, Console::ansiFormat("❌ {$message}\n", [Console::FG_RED, Console::BOLD]));
    }

    /**
     * Mensagem de sucesso em verde (stdout)
     */
    public static function success(string $message): void
    {
        fwrite(STDOUT, Console::ansiFormat("✔ {$message}\n", [Console::FG_GREEN, Console::BOLD]));
    }

    /**
     * Mensagem de aviso em amarelo (stdout)
     */
    public static function warning(string $message): void
    {
        fwrite(STDOUT, Console::ansiFormat("⚠ {$message}\n", [Console::FG_YELLOW, Console::BOLD]));
    }

    /**
     * Mensagem informativa em azul (stdout)
     */
    public static function info(string $message): void
    {
        fwrite(STDOUT, Console::ansiFormat("ℹ {$message}\n", [Console::FG_CYAN]));
    }

    /**
     * Mensagem simples em cinza (stdout)
     */
    public static function plain(string $message): void
    {
        fwrite(STDOUT, Console::ansiFormat("{$message}\n", [Console::FG_GREY]));
    }
}
