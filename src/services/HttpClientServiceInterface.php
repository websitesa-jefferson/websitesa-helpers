<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Services;

use yii\httpclient\Client;
use Websitesa\Yii2\Helpers\Dtos\HttpResponseDto;

interface HttpClientServiceInterface
{
    /**
     * Executa uma requisição HTTP autenticada.
     *
     * @param array<string, mixed> $data Dados a serem enviados (para POST/PUT)
     * @param array<string, mixed> $headers
     */
    public function client(
        string $method,
        string $baseUrl,
        string $endpoint,
        string $format = Client::FORMAT_JSON,
        string $contentType = HttpClientService::DEFAULT_CONTENT_TYPE,
        array $data = [],
        ?string $content = null,
        array $headers = [],
        bool $raw = false
    ): HttpResponseDto;
}
