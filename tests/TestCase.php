<?php

namespace Websitesa\Yii2\Helpers\Helper\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Yii;
use yii\base\NotSupportedException;

/**
 * This is the base class for all brvalidator unit tests.
 */
class TestCase extends PHPUnitTestCase
{
    /**
     * This method is called before the first test of this test class is run.
     * Attempts to load vendor autoloader.
     * @throws \yii\base\NotSupportedException
     */
    public static function setUpBeforeClass(): void
    {
        $vendorDir = __DIR__ . '/../vendor';
        $vendorAutoload = $vendorDir . '/autoload.php';
        if (file_exists($vendorAutoload)) {
            require_once($vendorAutoload);
        } else {
            throw new NotSupportedException("Vendor autoload file '{$vendorAutoload}' is missing.");
        }
        require_once($vendorDir . '/yiisoft/yii2/Yii.php');
        Yii::setAlias('@vendor', $vendorDir);
    }

    /**
     * Populates Yii::$app with a new application
     */
    protected function mockApplication()
    {
        static $config = [
            'id'       => 'testapp',
            'basePath' => __DIR__,
        ];
        $config['vendorPath'] = dirname(dirname(__DIR__)) . '/vendor';
        new \yii\console\Application($config);
    }

    /**
     * Sets up before test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
    }

    /**
     * Clean up after test.
     * The application created with [[mockApplication]] will be destroyed.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        Yii::$app = null;
    }
}
