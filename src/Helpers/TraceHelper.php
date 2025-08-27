<?php

declare(strict_types=1);

use yii\db\ActiveQuery;
use yii\db\Query;

/**
 * Exibe uma variável formatada para debug.
 *
 * @param mixed $obj Valor a ser exibido
 * @param bool $dump Se true, usa var_dump; se false, usa print_r
 * @param bool $break Se true, encerra a execução após exibir
 */
function trace(mixed $obj, bool $dump = false, bool $break = true): void
{
    echo "<pre>";
    if ($dump) {
        var_dump($obj);
    } else {
        echo print_r($obj, true);
    }
    echo "</pre>\n";

    if ($break) {
        exit;
    }
}

/**
 * Exibe o SQL gerado por uma query do Yii.
 *
 * @param bool $break Se true, encerra a execução após exibir
 */
function rawSql(Query|ActiveQuery $queryOrModel, bool $break = true): void
{
    echo $queryOrModel->createCommand()->rawSql . PHP_EOL;

    if ($break) {
        exit;
    }
}
