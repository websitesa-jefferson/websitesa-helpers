<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers;

use Yii;
use yii\base\InvalidValueException;

/**
 * @phpstan-type ApiResponseBase array{
 *   name: string,
 *   message: string,
 *   code: int,
 *   status: int
 * }
 */
class ResponseHelper
{
    /**
     * @param array<string, array<int, string>> $errors
     * @throws InvalidValueException
     */
    public static function formatErros(array $errors): string
    {
        if (count($errors) === 0) {
            throw new InvalidValueException('Array não pode ficar em branco.');
        }

        /** @var array<string, string> $response */
        $response = array_map(
            /** @param array<int, string> $error */
            fn (array $error): string => str_replace('"', '', $error[0] ?? ''),
            $errors
        );

        return implode(PHP_EOL, $response);
    }

    /**
     * @param array<string, mixed> $data
     * @return ApiResponseBase&array<string, mixed>
     */
    public static function sendResponse(
        string $name,
        string $message,
        int $code,
        int $status,
        array $data = []
    ): array {
        $responseData = [
            'name'    => $name,
            'message' => $message,
            'code'    => $code,
            'status'  => $status,
        ];

        if ($data !== []) {
            $responseData = array_merge($responseData, $data);
        }

        /** @var ApiResponseBase&array<string, mixed> $responseData */

        Yii::$app->response->setStatusCode($status);
        Yii::$app->response->data = $responseData;

        return $responseData;
    }
}
