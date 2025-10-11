<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Helper;

use Websitesa\Yii2\Helpers\Dto\ApiResponseDto;
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
     * @return array<string, mixed>
     */
    public static function sendResponse(
        string $name,
        string $message,
        int $code,
        int $status,
        array $data = []
    ): array {
        $dto = new ApiResponseDto($name, $message, $code, $status, $data);
        $dto->applyToResponse();

        return $dto->toArray();
    }
}
