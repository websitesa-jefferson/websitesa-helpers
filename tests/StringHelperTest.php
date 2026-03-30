<?php

declare(strict_types=1);

#php vendor/bin/phpunit tests/StringHelperTest.php --filter testCapitalizeNameNormalCase

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

    # capitalizeName()

    public function testCapitalizeNameNormalCase()
    {
        $this->assertEquals('Jefferson', StringHelper::capitalizeName('JEFFERSON'));
        $this->assertEquals('Jefferson Dias', StringHelper::capitalizeName('JEFFERSON DIAS'));
        $this->assertEquals('João da Silva', StringHelper::capitalizeName('joão DA silva'));
    }

    public function testCapitalizeNameWithPrepositions()
    {
        $this->assertEquals('Jefferson da Costa Dias', StringHelper::capitalizeName('JEFFERSON DA COSTA DIAS'));
        $this->assertEquals('Maria dos Santos e Silva', StringHelper::capitalizeName('maria dos santos e silva'));
        $this->assertEquals('Pedro de Almeida', StringHelper::capitalizeName('PEDRO DE ALMEIDA'));
        $this->assertEquals('Fulano del Toro', StringHelper::capitalizeName('FULANO DEL TORO'));
    }

    public function testCapitalizeNameWithFirstWordPreposition()
    {
        $this->assertEquals('Da Silva', StringHelper::capitalizeName('DA SILVA'));
        $this->assertEquals('De Paula', StringHelper::capitalizeName('DE PAULA'));
    }
}
