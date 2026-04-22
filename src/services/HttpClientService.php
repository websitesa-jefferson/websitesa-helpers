<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Services;

use Throwable;
use Websitesa\Yii2\Helpers\Dtos\HttpResponseDto;
use Websitesa\Yii2\Helpers\Helpers\RequestHelper;
use yii\base\InvalidArgumentException;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\httpclient\Response;

final class HttpClientService extends Client implements HttpClientServiceInterface
{
    public const CHARSET = 'utf-8';
    public const DEFAULT_CONTENT_TYPE = 'application/json';

    /** @inheritdoc */
    public function client(
        string $method,
        string $baseUrl,
        string $endpoint,
        string $format = self::FORMAT_JSON,
        string $contentType = self::DEFAULT_CONTENT_TYPE,
        array $data = [],
        ?string $content = null,
        array $headers = [],
        bool $raw = false
    ): HttpResponseDto {
        $token = RequestHelper::getTokenHeader();

        $defaultHeaders = [
            'Charset'      => self::CHARSET,
            'Content-Type' => $contentType,
        ];

        if ($token !== '') {
            $defaultHeaders['Authorization'] = $token;
        }

        $headers = array_merge($defaultHeaders, $headers);

        try {
            $this->transport = new CurlTransport();

            $this->requestConfig = [
                'options' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_TIMEOUT        => 30,
                    // CURLOPT_RESOLVE        => ['cmdi.websitesa.app.br:443:104.21.13.73'],
                ],
            ];

            $request = $this->createRequest()
            ->setFormat($format)
            ->setMethod($method)
            ->setUrl($baseUrl . $endpoint)
            ->setHeaders($headers)
            ->setData($data);

            if ($content !== null) {
                $request->setContent($content);
            }

            $send = $request->send();

            $response = $this->response($send, $raw);

            return $response;
        } catch (Throwable $t) {
            throw $t;
        }
    }

    private function response(Response $response, bool $raw = false): HttpResponseDto
    {
        $isOk = $response->getIsOk();
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $data = $raw ? [] : $this->resolveResponseData($response);
        $data = is_array($data) ? $data : ['data' => $data]; // garante tipo

        return $isOk
            ? HttpResponseDto::success($data, $statusCode, $content)
            : HttpResponseDto::error($data, $statusCode, $content);
    }

    /**
     * Normaliza os dados da resposta, mesmo quando o formato não é identificado pelo Yii.
     *
     * @return array<string, mixed>|mixed
     */
    private function resolveResponseData(Response $response): mixed
    {
        try {
            return $response->getData() ?? [];
        } catch (InvalidArgumentException) {
            $content = $response->getContent();

            if ($content !== '') {
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }

                return ['message' => trim($content)];
            }

            return [];
        }
    }
}
