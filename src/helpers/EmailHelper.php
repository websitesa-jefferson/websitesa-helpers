<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Helper;

use Yii;

class EmailHelper
{
    /**
     * @param string|array<string, string> $from
     * @param string|array<string, string> $to
     * @param string|array<string, string>|null $bcc
     * @param array<string, mixed> $params
     * @param array<int, string> $attachs
     */
    public static function sendMail(
        string|array $from,
        string|array $to,
        string $subject,
        string $view,
        string|array|null $bcc = [],
        array $params = [],
        array $attachs = []
    ): bool {
        try {
            /** @var \yii\mail\MailerInterface $mailer */
            $mailer = Yii::$app->mailer;

            /** @var \yii\mail\MessageInterface $message */
            $message = $mailer->compose($view, $params)
                ->setFrom($from)
                ->setTo($to)
                ->setSubject($subject);

            if ($bcc !== [] && $bcc !== null && $bcc !== '') {
                $message->setBcc($bcc);
            }

            foreach ($attachs as $attach) {
                if (file_exists($attach)) {
                    $message->attach($attach);
                }
            }

            return $message->send();
        } catch (\Throwable $e) {
            Yii::error('Erro ao enviar e-mail: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}
