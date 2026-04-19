<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Enum;

enum BaseEnum: string
{
    case Active = 'Ativo';
    case Inactive = 'Inativo';

    public function label(string $lang = 'pt'): string
    {
        return match ($lang) {
            'en' => match ($this) {
                self::Active   => 'Active',
                self::Inactive => 'Inactive',
            },
            default => $this->value,
        };
    }
}
