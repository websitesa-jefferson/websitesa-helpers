<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Enums;

enum BaseEnum: int
{
    case Active = 1;
    case Inactive = 2;

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
