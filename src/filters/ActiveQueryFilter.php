<?php

// Aplicar no model, não ficou bom na api
// /**
// * @return ActiveQueryFilter<Post>
// */
// public static function find()
// {
//     return new ActiveQueryFilter(get_called_class());
// }

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Filters;

use Websitesa\Yii2\Helpers\Helpers\CheckHelper;
use Yii;
use yii\db\ActiveQuery;

/**
 * @template T of \yii\db\ActiveRecord
 * @extends ActiveQuery<T>
 */
class ActiveQueryFilter extends ActiveQuery
{
    public function init(): void
    {
        parent::init();

        $userId = Yii::$app->user->id ?? null;
        $model = $this->modelClass;

        if (CheckHelper::valueExists($userId) === false) {
            return;
        }

        if (is_subclass_of($model, \yii\db\ActiveRecord::class)) {
            $this->andFilterWhere([$model::tableName() . ".created_by" => $userId]);
        }
    }
}
