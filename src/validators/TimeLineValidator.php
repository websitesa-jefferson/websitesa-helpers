<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Validator;

use Websitesa\Yii2\Helpers\Helper\CheckHelper;
use Websitesa\Yii2\Helpers\Helper\ValidationHelper;
use Yii;
use yii\base\Model;
use yii\validators\Validator;

final class TimeLineValidator extends Validator
{
    /**
     * Valida datas de início e fim e verifica duração.
     *
     * @param Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute): void
    {
        $start = $model->getAttribute('start');
        $end = $model->getAttribute('end');

        if (CheckHelper::valueExists($start, $end) === false) {
            return;
        }

        if (ValidationHelper::validateDateTime($start) === false) {
            $this->addError($model, $attribute, '"{attribute}" formato é inválido.');
            return;
        }

        $now = time();
        $startTimestamp = strtotime($start);
        $endTimestamp = strtotime($end);

        // Calcula duração em minutos
        $duration = Yii::$app->formatter->timeInMinutes(
            Yii::$app->formatter->asDate_($start, 'Y-m-d H:i:s'),
            Yii::$app->formatter->asDate_($end, 'Y-m-d H:i:s')
        );

        // Obtém valores antigos
        $startOld = $model->getOldAttribute('start');
        $endOld = $model->getOldAttribute('end');

        $startOldTimestamp = CheckHelper::valueExists($startOld) ? strtotime($startOld) : null;
        $endOldTimestamp = CheckHelper::valueExists($endOld) ? strtotime($endOld) : null;

        // Se houve alteração, aplica validações
        if ($startOldTimestamp !== $startTimestamp || $endOldTimestamp !== $endTimestamp) {

            if ($startTimestamp < $now) {
                $this->addError($model, $attribute, '"{attribute}" não pode ser menor que a data atual.');
            }

            if ($startTimestamp >= $endTimestamp) {
                $this->addError($model, $attribute, '"{attribute}" não pode ser maior ou igual ao término. Duração não pode ser 0.');
            }

            // if ($duration > 960) {
            //     $this->addError($model, $attribute, '"{attribute}" não pode ser superior a 16 horas de duração.');
            // }

            // if ($duration < 15) {
            //     $this->addError($model, $attribute, '"{attribute}" não pode ser inferior a 15 minutos de duração.');
            // }
        }
    }
}
