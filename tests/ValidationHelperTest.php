<?php

declare(strict_types=1);

#php ../vendor/bin/phpunit ValidationHelperTest:<teste>

namespace app\tests\unit;

use Websitesa\Yii2\Helpers\Helper\Tests\TestCase;
use Websitesa\Yii2\Helpers\Helper\ValidationHelper;
use Yii;
use yii\web\Request;

class ValidationHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Mock do Request
        $request = $this->createMock(Request::class);
        Yii::$app->set('request', $request);
    }

    # matchIP()

    public function testMatchIPExact()
    {
        Yii::$app->request->method('getUserIP')->willReturn('192.168.0.1');
        $this->assertTrue(ValidationHelper::matchIP(['192.168.0.1']));
        $this->assertFalse(ValidationHelper::matchIP(['192.168.0.2']));
    }

    public function testMatchIPWildcard()
    {
        Yii::$app->request->method('getUserIP')->willReturn('10.0.0.5');
        $this->assertTrue(ValidationHelper::matchIP(['*']));
    }

    public function testMatchIPPrefix()
    {
        Yii::$app->request->method('getUserIP')->willReturn('192.168.1.10');
        $this->assertTrue(ValidationHelper::matchIP(['192.168.1.*']));
        $this->assertFalse(ValidationHelper::matchIP(['192.168.2.*']));
    }

    public function testMatchIPEmptyList()
    {
        Yii::$app->request->method('getUserIP')->willReturn('1.2.3.4');
        $this->assertTrue(ValidationHelper::matchIP([]));
    }

    public function testMatchIPNull()
    {
        Yii::$app->request->method('getUserIP')->willReturn(null);
        $this->assertFalse(ValidationHelper::matchIP(['1.2.3.4']));
        $this->assertFalse(ValidationHelper::matchIP([]));
    }

    # validateDateTime()

    public function testValidateDateTimeValidDefaultFormat()
    {
        $this->assertTrue(ValidationHelper::validateDateTime('2025-08-13 14:30:00'));
    }

    public function testValidateDateTimeInvalidDefaultFormat()
    {
        $this->assertFalse(ValidationHelper::validateDateTime('2025-08-13')); // falta hora
        $this->assertFalse(ValidationHelper::validateDateTime('14:30:00'));    // falta data
        $this->assertFalse(ValidationHelper::validateDateTime('invalid'));
    }

    public function testValidateDateTimeCustomFormat()
    {
        $this->assertTrue(ValidationHelper::validateDateTime('13/08/2025', 'd/m/Y'));
        $this->assertFalse(ValidationHelper::validateDateTime('2025-08-13', 'd/m/Y'));
    }

    public function testValidateDateTimeLeapYear()
    {
        $this->assertTrue(ValidationHelper::validateDateTime('2024-02-29', 'Y-m-d')); // ano bissexto
        $this->assertFalse(ValidationHelper::validateDateTime('2023-02-29', 'Y-m-d')); // não bissexto
    }

    public function testValidateDateTimeInvalidFormatCharacters()
    {
        $this->assertFalse(ValidationHelper::validateDateTime('2025-13-01 12:00:00')); // mês inválido
        $this->assertFalse(ValidationHelper::validateDateTime('2025-00-10 12:00:00')); // mês inválido
        $this->assertFalse(ValidationHelper::validateDateTime('2025-12-32 12:00:00')); // dia inválido
    }

    # isValidCpf()

    public function testValidCpf(): void
    {
        // CPFs válidos conhecidos
        $this->assertTrue(ValidationHelper::isValidCpf('529.982.247-25'));
        $this->assertTrue(ValidationHelper::isValidCpf('11144477735'));

        // CPF com máscara e válido
        $this->assertTrue(ValidationHelper::isValidCpf('295.379.955-93'));
    }

    public function testInvalidCpfWrongDigits(): void
    {
        $this->assertFalse(ValidationHelper::isValidCpf('52998224726')); // último dígito errado
        $this->assertFalse(ValidationHelper::isValidCpf('11144477736')); // último dígito errado
    }

    public function testInvalidCpfRepeatedNumbers(): void
    {
        // Todos os dígitos iguais são inválidos
        $this->assertFalse(ValidationHelper::isValidCpf('11111111111'));
        $this->assertFalse(ValidationHelper::isValidCpf('00000000000'));
        $this->assertFalse(ValidationHelper::isValidCpf('99999999999'));
    }

    public function testInvalidCpfWrongLength(): void
    {
        $this->assertFalse(ValidationHelper::isValidCpf('1234567890'));   // 10 dígitos
        $this->assertFalse(ValidationHelper::isValidCpf('123456789012')); // 12 dígitos
        $this->assertFalse(ValidationHelper::isValidCpf(''));             // vazio
    }

    public function testInvalidCpfNonNumeric(): void
    {
        $this->assertFalse(ValidationHelper::isValidCpf('abc.def.ghi-jk'));
        $this->assertFalse(ValidationHelper::isValidCpf('52998224A25')); // letra no meio
    }

    # isValidCnpj()
    public function testValidCnpj(): void
    {
        // CNPJs válidos conhecidos
        $this->assertTrue(ValidationHelper::isValidCnpj('04.252.011/0001-10'));
        $this->assertTrue(ValidationHelper::isValidCnpj('33.000.167/0001-01'));
        $this->assertTrue(ValidationHelper::isValidCnpj('33.555.921/0001-70'));
    }

    public function testInvalidCnpjWrongDigits(): void
    {
        // Dígitos finais alterados
        $this->assertFalse(ValidationHelper::isValidCnpj('60.387.100/0001-99'));
        $this->assertFalse(ValidationHelper::isValidCnpj('11444777000162'));
    }

    public function testInvalidCnpjRepeatedNumbers(): void
    {
        // Todos os dígitos iguais são inválidos
        $this->assertFalse(ValidationHelper::isValidCnpj('11111111111111'));
        $this->assertFalse(ValidationHelper::isValidCnpj('00000000000000'));
        $this->assertFalse(ValidationHelper::isValidCnpj('99999999999999'));
    }

    public function testInvalidCnpjWrongLength(): void
    {
        $this->assertFalse(ValidationHelper::isValidCnpj('1234567890123'));   // 13 dígitos
        $this->assertFalse(ValidationHelper::isValidCnpj('123456789012345')); // 15 dígitos
        $this->assertFalse(ValidationHelper::isValidCnpj(''));                // vazio
    }

    public function testInvalidCnpjNonNumeric(): void
    {
        $this->assertFalse(ValidationHelper::isValidCnpj('AB.CDE.FGH/IJKL-MN'));
        $this->assertFalse(ValidationHelper::isValidCnpj('60.387.100/000A-04'));
    }
}
