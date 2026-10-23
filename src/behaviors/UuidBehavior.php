<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Behaviors;

use Websitesa\Yii2\Helpers\Helpers\UuidHelper;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\BaseActiveRecord;

/**
 * Behavior que preenche automaticamente o atributo de identificador universal único
 * com um UUIDv7 antes de inserir o registro no banco de dados.
 *
 * @extends Behavior<BaseActiveRecord>
 * @property BaseActiveRecord $owner
 */
final class UuidBehavior extends Behavior
{
    /**
     * Nome do atributo que armazenará o UUIDv7. Padrão: 'uid'.
     */
    public string $attribute = 'uid';

    /**
     * Se deve sobrescrever o valor existente mesmo que já tenha sido preenchido manualmente.
     */
    public bool $preserveNonEmptyValues = true;

    /**
     * @return array<string, string>
     */
    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
        ];
    }

    public function beforeInsert(Event $event): void
    {
        $owner = $this->owner;
        $currentValue = $owner->getAttribute($this->attribute);

        if (!$this->preserveNonEmptyValues || $currentValue === null || $currentValue === '') {
            $owner->setAttribute($this->attribute, UuidHelper::generateV7());
        }
    }
}
