<?php

declare(strict_types=1);

#php ../vendor/bin/phpunit ConvertHelperTest:<teste>

namespace Websitesa\Yii2\Helpers\Tests;

use Websitesa\Yii2\Helpers\Helper\ConvertHelper;
use Websitesa\Yii2\Helpers\Helper\Tests\TestCase;
use Yii;

/**
 * Classe fake para testes
 */
class TestConstants
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';
}

class ConvertHelperTest extends TestCase
{
    # covertTonull()

    public function testConvertToNull()
    {
        // Teste 1: valor 'null' deve virar null
        $input = ['null', 'valor', '123', ''];
        $expected = [null, 'valor', '123', ''];
        $this->assertEquals($expected, ConvertHelper::convertToNull($input));

        // Teste 2: array vazio retorna array vazio
        $input = [];
        $expected = [];
        $this->assertEquals($expected, ConvertHelper::convertToNull($input));

        // Teste 3: array sem 'null' permanece inalterado
        $input = ['a', 'b', 'c'];
        $expected = ['a', 'b', 'c'];
        $this->assertEquals($expected, ConvertHelper::convertToNull($input));

        // Teste 4: múltiplos 'null' no array
        $input = ['null', 'null', 'valor', 'null'];
        $expected = [null, null, 'valor', null];
        $this->assertEquals($expected, ConvertHelper::convertToNull($input));
    }

    # constantToArray()

    public function testConstantsToArrayWithoutPrefix()
    {
        $result = ConvertHelper::constantsToArray(TestConstants::class);

        $expected = [
            Yii::t('app', 'active'),
            Yii::t('app', 'inactive'),
            Yii::t('app', 'admin'),
            Yii::t('app', 'user'),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testConstantsToArrayWithPrefix()
    {
        $result = ConvertHelper::constantsToArray(TestConstants::class, 'STATUS_');

        $expected = [
            Yii::t('app', 'active'),
            Yii::t('app', 'inactive'),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testConstantsToArrayAssoc()
    {
        $result = ConvertHelper::constantsToArray(TestConstants::class, null, true);

        $expected = [
            'STATUS_ACTIVE'   => Yii::t('app', 'active'),
            'STATUS_INACTIVE' => Yii::t('app', 'inactive'),
            'ROLE_ADMIN'      => Yii::t('app', 'admin'),
            'ROLE_USER'       => Yii::t('app', 'user'),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testConstantsToArrayExcludingValues()
    {
        $result = ConvertHelper::constantsToArray(
            TestConstants::class,
            null,
            false,
            ['inactive', 'user']
        );

        $expected = [
            Yii::t('app', 'active'),
            Yii::t('app', 'admin'),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testConstantsToArrayWithPrefixAndExclusion()
    {
        $result = ConvertHelper::constantsToArray(
            TestConstants::class,
            'ROLE_',
            false,
            ['user']
        );

        $expected = [
            Yii::t('app', 'admin'),
        ];

        $this->assertEquals($expected, $result);
    }
}
