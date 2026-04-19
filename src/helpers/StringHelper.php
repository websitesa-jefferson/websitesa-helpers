<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Helpers;

use yii\helpers\BaseInflector;
use yii\helpers\BaseStringHelper;

class StringHelper extends BaseStringHelper
{
    /**
     * Returns a string with all spaces converted to given replacement,
     * non word characters removed and the rest of characters transliterated.
     * @param string $string An arbitrary string to convert.
     * @param string $replacement The replacement to use for spaces.
     * @param bool $lowercase whether to return the string in lowercase or not. Defaults to `true`.
     * @return string The converted string.
     */
    public static function slug(
        string $string,
        string $replacement = '-',
        bool $lowercase = true
    ): string {
        return BaseInflector::slug($string, $replacement, $lowercase);
    }

    /**
     * Capitalize a name properly, ignoring prepositions like "da", "de", "do", etc.
     */
    public static function capitalizeName(string $name): string
    {
        $words = explode(' ', mb_strtolower(trim($name), 'UTF-8'));
        $exceptions = ['de', 'da', 'do', 'das', 'dos', 'e', 'di', 'del', 'van', 'von'];

        $formattedWords = [];
        foreach ($words as $word) {
            if ($word === '') {
                $formattedWords[] = '';
                continue;
            }
            if ($formattedWords === [] || !in_array($word, $exceptions, true)) {
                $formattedWords[] = mb_convert_case($word, MB_CASE_TITLE, 'UTF-8');
            } else {
                $formattedWords[] = $word;
            }
        }

        return implode(' ', $formattedWords);
    }
}
