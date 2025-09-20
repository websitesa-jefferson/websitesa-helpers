<?php

declare(strict_types=1);

#php ../vendor/bin/phpunit RequestHelperTest:<teste>

namespace app\tests\unit;

use Websitesa\Yii2\Helpers\Helper\RequestHelper;
use Websitesa\Yii2\Helpers\Helper\Tests\TestCase;

class RequestHelperTest extends TestCase
{
    # getHostName()

    protected function tearDown(): void
    {
        // Limpa $_SERVER após cada teste
        $_SERVER = [];
        parent::tearDown();
    }

    public function testGetHostNameFromHttpHost()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SERVER_NAME'] = 'server.com';

        $this->assertEquals('example.com', RequestHelper::getHostName());
    }

    public function testGetHostNameFromServerName()
    {
        unset($_SERVER['HTTP_HOST']);
        $_SERVER['SERVER_NAME'] = 'server.com';

        $this->assertEquals('server.com', RequestHelper::getHostName());
    }

    public function testGetHostNameFromGetHostName()
    {
        unset($_SERVER['HTTP_HOST'], $_SERVER['SERVER_NAME']);

        $hostname = gethostname() ?: '';
        $this->assertEquals($hostname, RequestHelper::getHostName());
    }

    public function testGetHostNameEmpty()
    {
        unset($_SERVER['HTTP_HOST'], $_SERVER['SERVER_NAME']);

        // Mock temporário de gethostname usando closure wrapper se necessário
        // Aqui, assumimos que gethostname() retorna null ou vazio
        $this->assertIsString(RequestHelper::getHostName());
    }
}
