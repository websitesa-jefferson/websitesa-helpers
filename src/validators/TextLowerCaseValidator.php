<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Validators;

use Websitesa\Yii2\Helpers\Helpers\CheckHelper;
use yii\base\Model;
use yii\validators\Validator;

final class TextLowerCaseValidator extends Validator
{
    /**
     * Converte o texto do atributo para minúsculas.
     *
     * @param Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute): void
    {
        $value = $model->getAttribute($attribute);

        if (CheckHelper::valueExists($value)) {
            $model->setAttribute($attribute, mb_strtolower((string)$value, 'UTF-8'));
        }
    }
}
