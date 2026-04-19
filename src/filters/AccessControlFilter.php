<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Filter;

use Yii;
use yii\base\Action;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\rbac\ManagerInterface;
use yii\web\User;

/**
 * @extends AccessControl<\yii\web\User>
 */
class AccessControlFilter extends AccessControl
{
    /** @var Action<\yii\base\Controller> */
    protected Action $_action;

    public function init(): void
    {
        parent::init();
        // @phpstan-ignore-next-line Propriedade mágica Yii2
        $this->_action = Yii::$app->controller->action
            ?? throw new \RuntimeException('Ação não encontrada no controller.');
    }

    public function checkPermission(string $permission): bool
    {
        $wildcards = $this->mountWildcards();
        $userPermissions = $this->userPermissions();

        return in_array($permission, $userPermissions, true)
            || in_array($wildcards['root'], $userPermissions, true)
            || in_array($wildcards['module'], $userPermissions, true)
            || in_array($wildcards['controller'], $userPermissions, true);
    }

    /**
     * @return string[]
     */
    public function userPermissions(): array
    {
        $user = $this->user;

        if (!$user instanceof User) {
            return [];
        }

        $userId = $user->getId();
        if ($userId === null) {
            return [];
        }

        $auth = Yii::$app->authManager;
        if (!$auth instanceof ManagerInterface) {
            return [];
        }

        $permissions = array_merge(
            $auth->getPermissionsByUser($userId),
            $auth->getPermissionsByRole('guest')
        );

        return array_values(ArrayHelper::map($permissions, 'name', 'name'));
    }

    public function getRoute(): string
    {
        $route = '';

        // @phpstan-ignore-next-line Propriedade mágica Yii2
        foreach (Yii::$app->loadedModules as $module) {
            if (!in_array($module->id, Yii::$app->params['discardedModules'] ?? [], true)) {
                $route .= '/' . $module->id;
            }
        }

        $route .= '/' . $this->_action->controller->id . '/' . $this->_action->id;

        return preg_replace('#/+#', '/', $route) ?? '';
    }

    /**
     * @return array{root: string, module: string|null, controller: string|null}
     */
    public function mountWildcards(): array
    {
        $route = [
            'root'       => '/*',
            'module'     => null,
            'controller' => null,
        ];

        // @phpstan-ignore-next-line Propriedade mágica Yii2
        foreach (Yii::$app->loadedModules as $module) {
            if (!in_array($module->id, Yii::$app->params['discardedModules'] ?? [], true)) {
                $route['module'] = '/' . $module->id . '/*';
            }
        }

        if ($route['module'] !== null) {
            $module = str_replace('/*', '/', $route['module']);
            $route['controller'] = '/' . $module . '/' . $this->_action->controller->id . '/*';
        }

        return [
            'root'       => '/*',
            'module'     => $route['module'] !== null ? (string) preg_replace('#/+#', '/', $route['module']) : null,
            'controller' => $route['controller'] !== null ? (string) preg_replace('#/+#', '/', $route['controller']) : null,
        ];
    }
}
