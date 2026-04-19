<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Service;

interface PushNotificationServiceInterface
{
    /**
     * Subscribe a user to push notifications linked to a specific proxy.
     */
    public function subscribe(int $userId, string $endpoint, string $p256dh, string $auth, int $proxyId): bool;

    /**
     * Unsubscribe a user from push notifications.
     * If endpoint is null, unsubscribe all.
     */
    public function unsubscribe(int $userId, ?string $endpoint = null): bool;

    /**
     * Send a push notification to a user.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed> Result of sending (success count, failure count, details)
     */
    public function sendToUser(int $userId, array $payload): array;

    /**
     * Send a push notification to all users subscribed via a specific proxy.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed> Result of sending (success count, failure count, details)
     */
    public function sendToProxyUsers(int $proxyId, array $payload): array;

    /**
     * Send a single push notification by subscription ID.
     * Usually called by a background job.
     *
     * @param int $id The PushSubscription ID.
     * @param array<string, mixed> $payload The notification payload.
     * @return bool True if successfully sent.
     */
    public function sendBySubscriptionId(int $id, array $payload): bool;
}
