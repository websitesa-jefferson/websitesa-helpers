<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Tests;

use DateTimeImmutable;
use InvalidArgumentException;
use Websitesa\Yii2\Helpers\Helpers\UuidHelper;

class UuidHelperTest extends TestCase
{
    public function testGenerateV7ValidFormat(): void
    {
        $uuid = UuidHelper::generateV7();

        self::assertSame(36, strlen($uuid));
        self::assertTrue(UuidHelper::isValid($uuid));
        self::assertTrue(UuidHelper::isV7($uuid));

        // Partes do UUID: 8-4-4-4-12
        $parts = explode('-', $uuid);
        self::assertCount(5, $parts);
        self::assertSame(8, strlen($parts[0]));
        self::assertSame(4, strlen($parts[1]));
        self::assertSame(4, strlen($parts[2]));
        self::assertSame(4, strlen($parts[3]));
        self::assertSame(12, strlen($parts[4]));

        // RFC 9562: 3º bloco começa com 7
        self::assertSame('7', $parts[2][0]);

        // RFC 9562: 4º bloco começa com 8, 9, a ou b (variante 1)
        self::assertContains($parts[3][0], ['8', '9', 'a', 'b']);
    }

    public function testLexicalOrderingByTimestamp(): void
    {
        $uuid1 = UuidHelper::generateV7(1_700_000_000_000);
        $uuid2 = UuidHelper::generateV7(1_700_000_000_100);
        $uuid3 = UuidHelper::generateV7(1_700_000_001_000);

        self::assertLessThan(0, strcmp($uuid1, $uuid2));
        self::assertLessThan(0, strcmp($uuid2, $uuid3));
    }

    public function testGenerateV7Supports48BitTimestampBoundaries(): void
    {
        foreach ([0, 0xFFFFFFFFFFFF] as $timestampMs) {
            $uuid = UuidHelper::generateV7($timestampMs);

            self::assertSame($timestampMs, UuidHelper::getTimestampMs($uuid));
        }
    }

    public function testGenerateV7RejectsNegativeTimestamp(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UuidHelper::generateV7(-1);
    }

    public function testGenerateV7RejectsTimestampAbove48Bits(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UuidHelper::generateV7(0x1000000000000);
    }

    public function testTimestampExtraction(): void
    {
        $nowMs = (int) (microtime(true) * 1000);
        $uuid = UuidHelper::generateV7($nowMs);

        $extractedMs = UuidHelper::getTimestampMs($uuid);
        self::assertSame($nowMs, $extractedMs);

        $dateTime = UuidHelper::getDateTime($uuid);
        self::assertInstanceOf(DateTimeImmutable::class, $dateTime);
        self::assertSame((int) ($nowMs / 1000), $dateTime->getTimestamp());
    }

    public function testValidationRejectsInvalid(): void
    {
        self::assertFalse(UuidHelper::isValid('not-a-uuid'));
        self::assertFalse(UuidHelper::isValid('12345678-1234-1234-1234-12345678901z'));

        // UUIDv4 não é UUIDv7
        $uuidV4 = 'c8a1e2bf-798a-4c28-9d33-4f932e6a928e';
        self::assertTrue(UuidHelper::isValid($uuidV4));
        self::assertFalse(UuidHelper::isV7($uuidV4));

        $this->expectException(InvalidArgumentException::class);
        UuidHelper::getTimestampMs($uuidV4);
    }
}
