<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Validator;

use Websitesa\Yii2\Helpers\Helper\ValidationHelper;
use yii\base\Model;
use yii\validators\Validator;

final class CnpjValidator extends Validator
{
    /**
     * Valida atributo CNPJ no modelo.
     *
     * @param Model $model Modelo Yii2 em validação
     * @param string $attribute Nome do atributo a ser validado
     */
    public function validateAttribute($model, $attribute): void
    {
        if (!ValidationHelper::isValidCnpj((string) $model->$attribute)) {
            $this->addError($model, $attribute, 'CNPJ inválido.');
        }
    }
}
