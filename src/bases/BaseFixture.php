<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Bases;

use Websitesa\Yii2\Helpers\Helpers\UuidHelper;
use yii\test\ActiveFixture;

class BaseFixture extends ActiveFixture
{
    /**
     * @return array<string|int, array<string, mixed>>
     */
    protected function getData(): array
    {
        /** @var array<string|int, array<string, mixed>> $rows */
        $rows = parent::getData();

        foreach ($rows as &$row) {
            if (!isset($row['uid']) || $row['uid'] === '') {
                $row['uid'] = UuidHelper::generateV7();
            }
        }
        unset($row);

        return $rows;
    }
}
