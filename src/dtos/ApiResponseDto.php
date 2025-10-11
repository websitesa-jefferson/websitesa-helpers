<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Dto;

class ApiResponseDto
{
    public string $name;
    public string $message;
    public int $code;
    public int $status;
    /** @var array<string, mixed> */
    public array $data;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        string $name,
        string $message,
        int $code,
        int $status,
        array $data = []
    ) {
        $this->name = $name;
        $this->message = $message;
        $this->code = $code;
        $this->status = $status;
        $this->data = $data;
    }

    /**
     * Retorna a resposta como array associativo.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge([
            'name'    => $this->name,
            'message' => $this->message,
            'code'    => $this->code,
            'status'  => $this->status,
        ], $this->data);
    }

    /**
     * Define o objeto como resposta no Yii.
     */
    public function applyToResponse(): void
    {
        \Yii::$app->response->setStatusCode($this->status);
        \Yii::$app->response->data = $this->toArray();
    }
}
