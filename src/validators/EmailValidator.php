<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Validator;

use Websitesa\Yii2\Helpers\Helper\CheckHelper;
use yii\base\Model;
use yii\validators\Validator;

final class EmailValidator extends Validator
{
    /**
     * Valida o e-mail do modelo.
     *
     * @param Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute): void
    {
        $value = $model->getAttribute($attribute);

        if (CheckHelper::valueExists($value) && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->addError($model, $attribute, '"{attribute}" é inválido.');
        }
    }
}
