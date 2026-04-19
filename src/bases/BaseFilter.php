<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Bases;

use Websitesa\Yii2\Helpers\Filters\AccessControlFilter;

class BaseFilter extends AccessControlFilter
{
    public function beforeAction($action): bool
    {
        return $this->checkAccess();
    }

    private function checkAccess(): bool
    {
        $route = $this->getRoute();
        $wildcard = $this->mountWildcards();
        $permissions = $this->userPermissions();

        if (
            in_array($route, $permissions, true) ||
            in_array($wildcard['root'], $permissions, true) ||
            in_array($wildcard['module'], $permissions, true) ||
            in_array($wildcard['controller'], $permissions, true)
        ) {
            return true;
        }

        $user = $this->user;

        if ($user instanceof \yii\web\User && $user->getIsGuest()) {
            $user->loginRequired();
            return false;
        }

        if ($user instanceof \yii\web\User) {
            $this->denyAccess($user);
        }

        return false;
    }
}
