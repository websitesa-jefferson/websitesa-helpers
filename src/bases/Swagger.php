<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Bases;

use OpenApi\Attributes as OA;

#[OA\Info(title: "API Documentation", version: "1.0.0")]
#[OA\Server(url: HOST_NAME)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    name: "Authorization",
    in: "header",
    bearerFormat: "JWT",
    scheme: "bearer"
)]
trait Swagger
{
    public function swaggerDefinitions(): void
    {
    }
}
