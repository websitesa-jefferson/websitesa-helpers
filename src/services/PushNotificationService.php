<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Services;

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Websitesa\Yii2\Helpers\Jobs\PushNotificationJob;
use Websitesa\Yii2\Helpers\Models\PushSubscription;
use Yii;

class PushNotificationService implements PushNotificationServiceInterface
{
    /**
     * @var WebPush
     */
    private $webPush;

    public function __construct()
    {
        $auth = [
            'VAPID' => [
                'subject'    => (($subject = getenv('VAPID_SUBJECT')) !== false && $subject !== '') ? $subject : 'mailto:admin@localhost',
                'publicKey'  => ($pubKey = getenv('VAPID_PUBLIC_KEY')) !== false ? $pubKey : '',
                'privateKey' => ($privKey = getenv('VAPID_PRIVATE_KEY')) !== false ? $privKey : '',
            ],
        ];

        // Ensure keys are present or handle gracefully?
        // If keys are missing, WebPush might throw exception later or now.
        // For now, assume environment is set up correctly.

        $this->webPush = new WebPush($auth);
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe(int $userId, string $endpoint, string $p256dh, string $auth, int $proxyId): bool
    {
        $subscription = PushSubscription::findOne(['endpoint' => $endpoint]);

        if ($subscription === null) {
            $subscription = new PushSubscription();
            $subscription->endpoint = $endpoint;
        }

        $subscription->user_id = $userId;
        $subscription->proxy_id = $proxyId;
        $subscription->p256dh = $p256dh;
        $subscription->auth = $auth;
        $subscription->content_encoding = 'aes128gcm'; // Default

        return $subscription->save();
    }

    /**
     * {@inheritdoc}
     */
    public function unsubscribe(int $userId, ?string $endpoint = null): bool
    {
        if ($endpoint !== null && $endpoint !== '') {
            $subscription = PushSubscription::findOne(['user_id' => $userId, 'endpoint' => $endpoint]);
            if ($subscription !== null) {
                return (bool)$subscription->delete();
            }
            return false;
        }

        // Unsubscribe all devices for user
        PushSubscription::deleteAll(['user_id' => $userId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function sendToUser(int $userId, array $payload): array
    {
        $subscriptions = PushSubscription::findAll(['user_id' => $userId]);
        return $this->sendToSubscriptions($subscriptions, $payload);
    }

    /**
     * {@inheritdoc}
     */
    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function sendToProxyUsers(int $proxyId, array $payload): array
    {
        $subscriptions = PushSubscription::findAll(['proxy_id' => $proxyId]);

        if ($subscriptions === []) {
            return [
                'success' => 0,
                'failed'  => 0,
                'details' => [['status' => 'no_subscriptions_for_proxy']],
            ];
        }

        return $this->sendToSubscriptions($subscriptions, $payload);
    }

    /**
     * {@inheritdoc}
     */
    /**
     * @param array<string, mixed> $payload
     */
    public function sendBySubscriptionId(int $id, array $payload): bool
    {
        $sub = PushSubscription::findOne($id);
        if ($sub === null) {
            return false;
        }

        try {
            $webPushSubscription = Subscription::create([
                'endpoint'        => $sub->endpoint,
                'publicKey'       => $sub->p256dh,
                'authToken'       => $sub->auth,
                'contentEncoding' => $sub->content_encoding,
            ]);

            $payloadJson = json_encode($payload);
            if ($payloadJson === false) {
                return false;
            }

            $report = $this->webPush->sendOneNotification($webPushSubscription, $payloadJson);

            if (!$report->isSuccess()) {
                if ($report->isSubscriptionExpired()) {
                    $sub->delete();
                }
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Yii::error("Error sending push notification: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Helper to queue notifications to a list of subscriptions
     *
     * @param PushSubscription[] $subscriptions
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function sendToSubscriptions(array $subscriptions, array $payload): array
    {
        if ($subscriptions === []) {
            return [
                'success' => 0,
                'failed'  => 0,
                'queued'  => 0,
                'details' => [['status' => 'no_subscriptions']],
            ];
        }

        $queuedCount = 0;
        $details = [];

        foreach ($subscriptions as $sub) {
            Yii::$app->queue->push(new PushNotificationJob([
                'subscriptionId' => $sub->id,
                'payload'        => $payload,
            ]));
            $queuedCount++;
            $details[] = [
                'endpoint' => $sub->endpoint,
                'status'   => 'queued',
            ];
        }

        return [
            'success' => 0,
            'failed'  => 0,
            'queued'  => $queuedCount,
            'details' => $details,
        ];
    }
}
