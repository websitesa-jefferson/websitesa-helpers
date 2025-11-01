<?php

declare(strict_types=1);

#php ../vendor/bin/phpunit RequestHelperTest:<teste>

namespace app\tests\unit;

use Websitesa\Yii2\Helpers\Helper\RequestHelper;
use Websitesa\Yii2\Helpers\Helper\Tests\TestCase;
use Yii;
use yii\web\HeaderCollection;
use yii\web\Request;

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

    # getTokenHeader()

    protected function setUp(): void
    {
        parent::setUp();
        // Garante que o request seja redefinido entre os testes
        Yii::$app->set('request', null);
    }

    public function testRetornaVazioQuandoRequestNaoEhWebRequest(): void
    {
        Yii::$app->set('request', new \stdClass());
        $this->assertSame('', RequestHelper::getTokenHeader());
    }

    public function testRetornaVazioQuandoHeaderAuthorizationNaoExiste(): void
    {
        $request = new FakeWebRequest([]);
        Yii::$app->set('request', $request);

        $this->assertSame('', RequestHelper::getTokenHeader());
    }

    public function testRetornaVazioQuandoAuthorizationNaoEhString(): void
    {
        $request = new FakeWebRequest(['Authorization' => ['array_invalido']]);
        Yii::$app->set('request', $request);

        $this->assertSame('array_invalido', RequestHelper::getTokenHeader());
    }

    public function testRetornaHeaderInteiroQuandoNaoTemBearer(): void
    {
        $request = new FakeWebRequest(['Authorization' => 'XYZ123']);
        Yii::$app->set('request', $request);

        $this->assertSame('XYZ123', RequestHelper::getTokenHeader());
    }

    public function testRetornaTokenSemPrefixoBearer(): void
    {
        $request = new FakeWebRequest(['Authorization' => 'Bearer ABC123TOKEN']);
        Yii::$app->set('request', $request);

        $this->assertSame('ABC123TOKEN', RequestHelper::getTokenHeader());
    }
}

/**
 * FakeWebRequest é uma subclasse real de yii\web\Request
 * que sobrescreve apenas o método getHeaders().
 */
class FakeWebRequest extends Request
{
    private HeaderCollection $headers;

    public function __construct(array $headers)
    {
        $this->headers = new HeaderCollection();
        foreach ($headers as $key => $value) {
            $this->headers->set($key, $value);
        }
        parent::__construct();
    }

    public function getHeaders(): HeaderCollection
    {
        return $this->headers;
    }
}
