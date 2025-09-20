<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Helper;

use Yii;

class ConvertHelper
{
    /**
     * Converte 'null' para null.
     *
     * @phpstan-param array<string, mixed> $values
     * @phpstan-return array<string, mixed>
     */
    public static function convertToNull(array $values): array
    {
        return array_map(
            fn ($value) => $value === 'null' ? null : $value,
            $values
        );
    }

    /**
     * Retorna um array com as constantes de uma classe, opcionalmente filtradas por prefixo.
     *
     * @param class-string $className Nome totalmente qualificado da classe.
     * @param string|null $prefix Prefixo para filtrar constantes (ex: "KEY_"), ou null para todas.
     * @param bool $assoc Se true, retorna array associativo [valor => tradução].
     * @param array<int, string|int> $excludeValues Lista de valores a serem ignorados.
     *
     * @return array<int, string>|array<string|int, string>
     *
     * @throws \ReflectionException Se a classe não existir.
     */
    public static function constantsToArray(
        string $className,
        ?string $prefix = null,
        bool $assoc = false,
        array $excludeValues = []
    ): array {
        $constants = (new \ReflectionClass($className))->getConstants();

        /** @var array<string, string|int> $filtered */
        $filtered = array_filter(
            $constants,
            fn ($value, $name): bool =>
                ($prefix === null || str_starts_with($name, $prefix))
                && !in_array($value, $excludeValues, true),
            ARRAY_FILTER_USE_BOTH
        );

        $translate = static fn (string|int $value): string => Yii::t('app', (string) $value);

        if ($assoc) {
            $assocResult = [];
            foreach ($filtered as $name => $value) {
                $assocResult[$name] = $translate($value);
            }

            return $assocResult;
        }

        return array_map($translate, array_values($filtered));
    }
}
