<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Validator;

use Websitesa\Yii2\Helpers\Helper\ValidationHelper;
use yii\base\Model;
use yii\validators\Validator;

class CpfValidator extends Validator
{
    /**
     * Valida atributo CPF no modelo.
     *
     * @param Model $model Modelo Yii2 em validação
     * @param string $attribute Nome do atributo a ser validado
     */
    public function validateAttribute($model, $attribute): void
    {
        if (!ValidationHelper::isValidCpf((string) $model->$attribute)) {
            $this->addError($model, $attribute, 'CPF inválido.');
        }
    }
}
