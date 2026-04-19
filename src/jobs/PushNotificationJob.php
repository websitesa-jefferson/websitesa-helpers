<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Jobs;

use Websitesa\Yii2\Helpers\Services\PushNotificationService;
use yii\queue\JobInterface;

/**
 * Job for sending push notifications in the background.
 */
class PushNotificationJob implements JobInterface
{
    /** @var int */
    public $subscriptionId;

    /** @var array<string, mixed> */
    public $payload;

    /**
     * @param array{
     *   subscriptionId?: int,
     *   payload?: array<string, mixed>
     * } $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Executes the push notification sending.
     * @param \yii\queue\Queue $queue
     */
    public function execute($queue)
    {
        $service = new PushNotificationService();
        $service->sendBySubscriptionId($this->subscriptionId, $this->payload);
        return null;
    }
}
