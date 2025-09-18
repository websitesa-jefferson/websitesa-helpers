<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Helper;

use Yii;
use yii\httpclient\Client;
use yii\httpclient\Response;

final class WebhookHelper
{
    /**
     * Envia dados para os webhooks configurados em Yii::$app->params['urlReceive'].
     *
     * @param array<string, mixed> $data Dados a serem enviados no corpo JSON
     *                                   (qualquer estrutura serializável em JSON)
     */
    public static function send(array $data = []): void
    {
        $urlsParam = Yii::$app->params['urlReceive'] ?? [];

        // Garante array de strings não vazias e ajuda o PHPStan a inferir
        $urls = array_values(array_filter(
            is_array($urlsParam) ? $urlsParam : [],
            static fn ($u): bool => is_string($u) && $u !== ''
        ));

        /** @var list<non-empty-string> $urls */

        if (count($urls) === 0) {
            Yii::warning('Nenhuma URL de webhook configurada.');
            return;
        }

        $client = new Client();

        foreach ($urls as $url) {
            try {
                /** @var Response $response */
                $response = $client->createRequest()
                    ->setFormat(Client::FORMAT_JSON)
                    ->setMethod('POST') // POST para corpo JSON
                    ->setUrl($url)
                    ->setHeaders(['Content-Type' => 'application/json'])
                    ->setData($data)
                    ->send();

                if ($response->getIsOk() === false) {
                    Yii::error(sprintf(
                        'Erro ao enviar webhook para %s: [%d] %s',
                        $url,
                        $response->getStatusCode(),
                        $response->getContent()
                    ));
                }
            } catch (\Throwable $e) {
                Yii::error(sprintf(
                    'Exceção ao enviar webhook para %s: %s',
                    $url,
                    $e->getMessage()
                ));
            }
        }
    }
}
