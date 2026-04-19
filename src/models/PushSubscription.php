<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Models;

use Websitesa\Yii2\Helpers\Bases\BaseActiveRecord;

/**
 * This is the model class for table "{{%push_subscription}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $proxy_id
 * @property string $endpoint
 * @property string $p256dh
 * @property string $auth
 * @property string $content_encoding
 */
class PushSubscription extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%push_subscription}}';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'endpoint', 'p256dh', 'auth'], 'required'],
            [['user_id', 'proxy_id'], 'integer'],
            [['endpoint'], 'string'],
            [['p256dh', 'auth', 'content_encoding'], 'string', 'max' => 255],
            [['endpoint'], 'unique'],
        ];
    }
}
