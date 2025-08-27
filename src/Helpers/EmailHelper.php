<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers;

use Yii;

class EmailHelper
{
    /**
     * Envia e-mail usando o mailer do Yii2.
     *
     *
     * @phpstan-param string|array<int, string>|array<string, string> $to   Email único, lista de emails ou mapa email=>nome
     * @phpstan-param array<int, string>|array<string, string> $bcc         Lista de emails ou mapa email=>nome
     * @phpstan-param array<string, mixed> $params                          Parâmetros usados no view do Yii
     * @phpstan-param list<string> $attachs                                 Caminhos de arquivos para anexar
     */
    public static function sendMail(
        string $from,
        string|array $to,
        string $subject,
        string $view,
        array $bcc = [],
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

            if ($bcc !== []) {
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
