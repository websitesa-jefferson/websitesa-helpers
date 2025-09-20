<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Validator;

use yii\base\Model;
use yii\validators\Validator;

final class FullNameValidator extends Validator
{
    /**
     * Valida e cria o registro caso não exista.
     *
     * @param Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute): void
    {
        $value = (string) $model->$attribute;

        // Extrai palavras (considerando Unicode)
        preg_match_all('/\w+/u', $value, $matches);

        if (count($matches[0]) < 2) {
            $this->addError($model, $attribute, '"{attribute}" nome completo deve ser informado.');
        }
    }
}
