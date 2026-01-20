<?php

#php ../vendor/bin/phpunit FormatterHelperTest:<teste>

namespace app\tests\unit;

use Websitesa\Yii2\Helpers\Helper\FormatterHelper;
use Websitesa\Yii2\Helpers\Helper\Tests\TestCase;
use yii\base\InvalidValueException;

class FormatterHelperTest extends TestCase
{
    # timeInMinutes()

    public function testTimeInMinutesExact()
    {
        $start = '2025-08-11 12:00:00';
        $end = '2025-08-11 12:30:00';

        $result = FormatterHelper::timeInMinutes($start, $end);

        $this->assertEquals(30, $result);
    }

    public function testTimeInMinutesWithSecondsRoundingUp()
    {
        $start = '2025-08-11 12:00:00';
        $end = '2025-08-11 12:30:31';

        $result = FormatterHelper::timeInMinutes($start, $end);

        $this->assertEquals(31, $result);
    }

    public function testTimeInMinutesWithSecondsNoRounding()
    {
        $start = '2025-08-11 12:00:00';
        $end = '2025-08-11 12:30:29';

        $result = FormatterHelper::timeInMinutes($start, $end);

        $this->assertEquals(30, $result);
    }

    public function testTimeInMinutesMultipleDays()
    {
        $start = '2025-08-09 12:00:00';
        $end = '2025-08-11 12:00:00';

        $result = FormatterHelper::timeInMinutes($start, $end);

        // 2 dias * 24h * 60min = 2880
        $this->assertEquals(2880, $result);
    }

    public function testInvalidStartDate()
    {
        $this->expectException(InvalidValueException::class);
        FormatterHelper::timeInMinutes('invalid-date', '2025-08-11 12:00:00');
    }

    public function testInvalidEndDate()
    {
        $this->expectException(InvalidValueException::class);
        FormatterHelper::timeInMinutes('2025-08-11 12:00:00', 'invalid-date');
    }

    # asCpf()

    public function testAsCpfValid()
    {
        $cpf = '12345678909';
        $formatted = FormatterHelper::asCpf($cpf);
        $this->assertEquals('123.456.789-09', $formatted);
    }

    public function testAsCpfWithNonDigits()
    {
        // Testa se a função remove caracteres não numéricos
        $cpf = '123.456.789-09';
        $formatted = FormatterHelper::asCpf($cpf);
        $this->assertEquals('123.456.789-09', $formatted);
    }

    public function testAsCpfInvalidTooShort()
    {
        $this->expectException(InvalidValueException::class);
        FormatterHelper::asCpf('123456789'); // 9 dígitos só
    }

    public function testAsCpfInvalidTooLong()
    {
        $this->expectException(InvalidValueException::class);
        FormatterHelper::asCpf('1234567890123'); // 13 dígitos
    }

    # asDate_()

    public function testAsDateValidReturnsFormatted()
    {
        $date = '11/08/2025';
        $result = FormatterHelper::asDate_($date, 'Y-m-d');
        $this->assertEquals('2025-08-11', $result);
    }

    public function testAsDateValidReturnsTimestamp()
    {
        $date = '2025-08-11';
        $result = FormatterHelper::asDate_($date, 'Y-m-d', true);
        $this->assertIsInt($result);

        // Verifica se o timestamp corresponde à data correta
        $this->assertEquals('2025-08-11', date('Y-m-d', $result));
    }

    public function testAsDateEmptyThrowsException()
    {
        $this->expectException(InvalidValueException::class);
        FormatterHelper::asDate_('');
    }

    public function testAsDateInvalidThrowsException()
    {
        $this->expectException(InvalidValueException::class);
        FormatterHelper::asDate_('data inválida');
    }

    # asInteger_()

    public function testAsIntegerNullOnEmpty()
    {
        $this->assertIsString(FormatterHelper::asInteger_(null));
        $this->assertIsString(FormatterHelper::asInteger_(''));
        $this->assertIsString(FormatterHelper::asInteger_('0'));
    }

    public function testAsIntegerRemovesNonDigits()
    {
        $input = 'a1b2c3d4e5';
        $expected = '12345';
        $this->assertEquals($expected, FormatterHelper::asInteger_($input));
    }

    public function testAsIntegerOnlyDigits()
    {
        $input = '09876543210';
        $this->assertEquals($input, FormatterHelper::asInteger_($input));
    }

    # secondsToTime()

    public function testSecondsToTimeExactHours()
    {
        $this->assertEquals('01:00:00', FormatterHelper::secondsToTime(3600));
        $this->assertEquals('02:00:00', FormatterHelper::secondsToTime(7200));
    }

    public function testSecondsToTimeHoursMinutesSeconds()
    {
        $this->assertEquals('01:01:01', FormatterHelper::secondsToTime(3661));
        $this->assertEquals('00:01:30', FormatterHelper::secondsToTime(90));
        $this->assertEquals('00:00:45', FormatterHelper::secondsToTime(45));
    }

    public function testSecondsToTimeZero()
    {
        $this->assertEquals('00:00:00', FormatterHelper::secondsToTime(0));
    }
}
