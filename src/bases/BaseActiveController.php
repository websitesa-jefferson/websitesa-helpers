<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Bases;

use andreyv\ratelimiter\IpRateLimiter;
use light\swagger\SwaggerAction;
use light\swagger\SwaggerApiAction;
use sizeg\jwt\JwtHttpBearerAuth;
use Websitesa\Yii2\Helpers\Helpers\RequestHelper;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

defined('HOST_NAME') or define('HOST_NAME', RequestHelper::getHostName());

class BaseActiveController extends ActiveController
{
    use Swagger;

    // @phpstan-ignore-next-line Propriedade mágica Yii2
    public $serializer = [
        'class'              => 'yii\rest\Serializer',
        'collectionEnvelope' => null,
    ];

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        unset($behaviors['authenticator'], $behaviors['rateLimiter']);

        if (!YII_ENV_TEST) {
            $behaviors['rateLimiter'] = [
                'class'      => IpRateLimiter::class,
                'rateLimit'  => 60,
                'timePeriod' => 60,
            ];
        }

        $token = RequestHelper::getTokenHeader();

        if (strlen($token) === 32) {
            $behaviors['authenticator'] = [
                'class'  => HttpBearerAuth::class,
                'except' => Yii::$app->params['except'],
            ];
        } else {
            $behaviors['authenticator'] = [
                'class'  => JwtHttpBearerAuth::class,
                'except' => Yii::$app->params['except'],
            ];
        }

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only'  => Yii::$app->params['actions'],
            'rules' => [
                [
                    'allow'       => true,
                    'controllers' => Yii::$app->params['controllers'],
                    'actions'     => Yii::$app->params['actions'],
                    'roles'       => ['?', '@'], // usuario convidado e autenticado
                ],
            ],
            'denyCallback' => function ($rule, $action) {
                throw new ForbiddenHttpException('Você não tem permissão para executar esta ação.');
            },
        ];

        if (is_array($behaviors['contentNegotiator'])) {
            $behaviors['contentNegotiator']['formats']['text/html'] =
                ArrayHelper::isIn($this->action?->id, ['api-docs', 'api-docs_'])
                ? Response::FORMAT_HTML
                : Response::FORMAT_JSON;
        }

        if (ArrayHelper::isIn($this->action?->id, ['api-docs', 'api-docs_'])) {
            Yii::$app->cache?->flush();
        }

        return $behaviors;
    }

    public function actions(): array
    {
        $actions = parent::actions();

        $actions['api-docs'] = [
            'class'   => SwaggerAction::class,
            'restUrl' => Url::to(['api-json'], true),
        ];

        $actions['api-json'] = [
            'class'   => SwaggerApiAction::class,
            'scanDir' => [
                Yii::getAlias('@app/modules/v1'),
            ],
        ];

        $actions['api-docs_'] = [
            'class'   => SwaggerAction::class,
            'restUrl' => Url::to(['api-json_'], true),
        ];

        $actions['api-json_'] = [
            'class'   => SwaggerApiAction::class,
            'scanDir' => [
                Yii::getAlias('@app/models'),
                Yii::getAlias('@app/controllers'),
            ],
        ];

        if (isset($actions['index']) && is_array($actions['index'])) {
            $actions['index']['prepareDataProvider'] = [$this, 'loadDataProvider'];
        }

        unset(
            $actions['view'],
            $actions['create'],
            $actions['update'],
            $actions['delete']
        );

        return $actions;
    }
}
