<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Helper;

use Yii;
use yii\console\Exception;
use yii\rbac\ManagerInterface;

class CheckHelper
{
    /**
     * Verifica se todos os valores passados existem e não estão vazios (null, '', 0 são considerados vazios).
     * @param mixed ...$values Um ou mais valores para verificar
     * @return bool true se todos existirem e não estiverem vazios, false caso contrário
     */
    public static function valueExists(mixed ...$values): bool
    {
        foreach ($values as $value) {
            if ($value === null || $value === '' || $value === false || $value === []) {
                return false;
            }
        }
        return true;
    }

    /**
     * Verifica se o diretório existe, caso não exista, cria com o modo informado.
     *
     * @param string $dir Diretório a ser criado
     * @param int $mode Permissões do diretório (padrão 0777)
     * @param bool $recursive Cria diretórios recursivamente (padrão true)
     * @return bool true se diretório foi criado, false se já existia
     * @throws Exception Caso não consiga criar o diretório
     */
    public static function createDir(string $dir, int $mode = 0777, bool $recursive = true): bool
    {
        if (is_dir($dir) === false) {
            if (mkdir($dir, $mode, $recursive) === false && !is_dir($dir)) {
                // mkdir pode falhar mas diretório existir (corrente condição de corrida)
                throw new Exception("Diretório '{$dir}' não foi criado.");
            }
            chmod($dir, $mode);
            return true;
        }
        return false;
    }

    /**
     * Verifica se o usuário possui permissão ou papel informado
     *
     * @param string $name Nome da permissão ou papel
     * @param 'getPermissionsByUser'|'getRolesByUser' $type Tipo de verificação
     * @return bool true se o usuário possui, false caso contrário
     */
    public static function can(string $name, string $type): bool
    {
        $userId = Yii::$app->user->getId();

        if ($userId === null) {
            return false;
        }

        $authManager = Yii::$app->authManager;

        if ($authManager instanceof ManagerInterface === false) {
            throw new Exception("AuthManager não está configurado.");
        }

        $authItems = match ($type) {
            'getPermissionsByUser' => $authManager->getPermissionsByUser($userId),
            'getRolesByUser'       => $authManager->getRolesByUser($userId)
        };

        return isset($authItems[$name]);
    }
}
