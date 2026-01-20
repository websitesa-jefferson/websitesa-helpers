<?php

declare(strict_types=1);

#php ../vendor/bin/phpunit CheckHelperTest:<teste>

namespace app\tests\unit;

use Websitesa\Yii2\Helpers\Helper\CheckHelper;
use Websitesa\Yii2\Helpers\Helper\Tests\TestCase;

class CheckHelperTest extends TestCase
{
    private string $tmpDir = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpDir = sys_get_temp_dir() . '/test_dir_' . uniqid();
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tmpDir)) {
            rmdir($this->tmpDir);
        }
        parent::tearDown();
    }

    # valueExists()

    public function testValueExistsAllValid()
    {
        $this->assertTrue(CheckHelper::valueExists('teste', 123, [1, 2, 3]));
        $this->assertTrue(CheckHelper::valueExists(1, 'a', true));
    }

    public function testValueExistsWithNull()
    {
        $this->assertFalse(CheckHelper::valueExists('teste', null));
    }

    public function testValueExistsWithEmptyString()
    {
        $this->assertFalse(CheckHelper::valueExists('teste', ''));
    }

    public function testValueExistsWithZero()
    {
        $this->assertTrue(CheckHelper::valueExists('teste', 0));
        $this->assertTrue(CheckHelper::valueExists(0));
    }

    public function testValueExistsWithEmptyArray()
    {
        $this->assertFalse(CheckHelper::valueExists([]));
    }

    public function testValueExistsSingleValue()
    {
        $this->assertTrue(CheckHelper::valueExists('teste'));
        $this->assertFalse(CheckHelper::valueExists(''));
    }

    # createDir()

    public function testCreateDirSuccessfully()
    {
        $result = CheckHelper::createDir($this->tmpDir, 0755);
        $this->assertTrue($result);
        $this->assertDirectoryExists($this->tmpDir);
        $this->assertEquals('0755', substr(sprintf('%o', fileperms($this->tmpDir)), -4));
    }

    public function testCreateDirAlreadyExists()
    {
        mkdir($this->tmpDir, 0755);
        $result = CheckHelper::createDir($this->tmpDir, 0755);
        $this->assertFalse($result);
    }
}
