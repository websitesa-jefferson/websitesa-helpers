<?php

declare(strict_types=1);

#php ../vendor/bin/phpunit StringHelperTest:<teste>

namespace app\tests\unit;

use Websitesa\Yii2\Helpers\Helper\StringHelper;
use Websitesa\Yii2\Helpers\Helper\Tests\TestCase;
use yii\helpers\BaseInflector;

class StringHelperTest extends TestCase
{
    # slug()

    public function testSlugSimpleString()
    {
        $string = 'Minha String';
        $expected = BaseInflector::slug($string);
        $this->assertEquals($expected, StringHelper::slug($string));
    }

    public function testSlugWithSpecialCharacters()
    {
        $string = 'Olá, Mundo! Como está?';
        $expected = BaseInflector::slug($string);
        $this->assertEquals($expected, StringHelper::slug($string));
    }

    public function testSlugWithReplacement()
    {
        $string = 'Minha String Teste';
        $replacement = '_';
        $expected = BaseInflector::slug($string, $replacement);
        $this->assertEquals($expected, StringHelper::slug($string, $replacement));
    }

    public function testSlugWithoutLowercase()
    {
        $string = 'Minha String Teste';
        $expected = BaseInflector::slug($string, '-', false);
        $this->assertEquals($expected, StringHelper::slug($string, '-', false));
    }

    public function testSlugEmptyString()
    {
        $string = '';
        $expected = BaseInflector::slug($string);
        $this->assertEquals($expected, StringHelper::slug($string));
    }
}
