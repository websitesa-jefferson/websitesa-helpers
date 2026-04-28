<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Bases;

// @TODO Remover após desenvolvimento do sistema
// if (in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) === false) {
//     die('You are not allowed to access this service.');
// }

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\BlameableBehavior;
use yii\caching\CacheInterface;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;

/**
 * @property int|null $status_id
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 */
class BaseActiveRecord extends ActiveRecord
{
    public const CACHE_TAG = '';
    public ?int $createdAt = null;
    public ?int $createdBy = null;
    public ?int $updatedAt = null;
    public ?int $updatedBy = null;

    public function init(): void
    {
        parent::init();
    }

    /**
     * @param bool $insert
     * @param array<array-key, mixed> $changedAttributes
     */
    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);
        $cache = Yii::$app->cache;
        if ($cache instanceof CacheInterface) {
            TagDependency::invalidate($cache, static::CACHE_TAG);
        }
    }

    public function afterDelete(): void
    {
        parent::afterDelete();
        $cache = Yii::$app->cache;
        if ($cache instanceof CacheInterface) {
            TagDependency::invalidate($cache, static::CACHE_TAG);
        }
    }

    public function behaviors(): array
    {
        return [
            [
                'class'              => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class'      => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => time(),
            ],
        ];
    }

    public function getStatusId(): ?int
    {
        return $this->status_id;
    }

    public function setStatusId(?int $value): void
    {
        $this->status_id = $value;
    }

    public function getCreatedAt(): ?int
    {
        return $this->created_at;
    }

    public function setCreatedAt(?int $value): void
    {
        $this->created_at = $value;
    }

    public function getCreatedBy(): ?int
    {
        return $this->created_by;
    }

    public function setCreatedBy(?int $value): void
    {
        $this->created_by = $value;
    }

    public function getUpdatedAt(): ?int
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?int $value): void
    {
        $this->updated_at = $value;
    }

    public function getUpdatedBy(): ?int
    {
        return $this->updated_by;
    }

    public function setUpdatedBy(?int $value): void
    {
        $this->updated_by = $value;
    }
}
