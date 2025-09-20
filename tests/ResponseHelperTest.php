<?php

declare(strict_types=1);

#php ../vendor/bin/phpunit ResponseHelperTest:<teste>

namespace app\tests\unit;

use Websitesa\Yii2\Helpers\Helper\ResponseHelper;
use Websitesa\Yii2\Helpers\Helper\Tests\TestCase;
use Yii;
use yii\base\InvalidValueException;
use yii\web\Response;

class ResponseHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Mock da resposta
        Yii::$app->set('response', new Response());
    }

    # formatErros()

    public function testFormatErrosNormal()
    {
        $errors = [
            ['Erro 1'],
            ['Erro 2'],
            ['Erro 3'],
        ];

        $expected = "Erro 1" . PHP_EOL . "Erro 2" . PHP_EOL . "Erro 3";

        $this->assertEquals($expected, ResponseHelper::formatErros($errors));
    }

    public function testFormatErrosRemovendoAspas()
    {
        $errors = [
            ['"Erro com aspas"'],
            ['Outro "erro" aqui'],
        ];

        $expected = "Erro com aspas" . PHP_EOL . "Outro erro aqui";

        $this->assertEquals($expected, ResponseHelper::formatErros($errors));
    }

    public function testFormatErrosArrayVazioLancandoExcecao()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Array não pode ficar em branco.');

        ResponseHelper::formatErros([]);
    }

    # sendResponse()

    public function testSendResponseWithoutData()
    {
        $name = 'Test';
        $message = 'Mensagem de teste';
        $code = 100;
        $status = 200;

        $result = ResponseHelper::sendResponse($name, $message, $code, $status);

        $expected = [
            'name'    => $name,
            'message' => $message,
            'code'    => $code,
            'status'  => $status,
        ];

        $this->assertEquals($expected, $result);
        $this->assertEquals($status, Yii::$app->response->statusCode);
        $this->assertEquals($expected, Yii::$app->response->data);
    }

    public function testSendResponseWithData()
    {
        $name = 'Test';
        $message = 'Mensagem de teste';
        $code = 100;
        $status = 201;
        $extraData = ['foo' => 'bar', 'baz' => 123];

        $result = ResponseHelper::sendResponse($name, $message, $code, $status, $extraData);

        $expected = array_merge([
            'name'    => $name,
            'message' => $message,
            'code'    => $code,
            'status'  => $status,
        ], $extraData);

        $this->assertEquals($expected, $result);
        $this->assertEquals($status, Yii::$app->response->statusCode);
        $this->assertEquals($expected, Yii::$app->response->data);
    }
}
