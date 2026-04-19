<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Dto;

final readonly class HttpResponseDto
{
    /**
     * @param bool $status Indica se a requisição foi bem-sucedida (HTTP 2xx)
     * @param array<string, mixed> $data Dados retornados pela API
     * @param int $statusCode Código de status HTTP
     * @param string|null $message Mensagem de erro (se houver)
     * @param int|string|null $code Código do erro retornado pela API (se houver)
     */
    public function __construct(
        public bool $status,
        public array $data,
        public string|int $statusCode,
        public ?string $message = null,
        public int|string|null $code = null,
        public mixed $body = null
    ) {
    }

    /**
     * Cria uma instância de sucesso.
     *
     * @param array<string, mixed> $data
     */
    public static function success(array $data, string|int $statusCode = 200, mixed $body = null): self
    {
        return new self(true, $data, $statusCode, null, null, $body);
    }

    /**
     * Cria uma instância de erro.
     *
     * @param array<string, mixed> $data
     */
    public static function error(array $data, string|int $statusCode, mixed $body = null): self
    {
        return new self(
            false,
            $data,
            $statusCode,
            is_string($data['message'] ?? null) ? $data['message'] : 'Erro desconhecido',
            (is_int($data['code'] ?? null) || is_string($data['code'] ?? null)) ? $data['code'] : $statusCode,
            $body
        );
    }
}
