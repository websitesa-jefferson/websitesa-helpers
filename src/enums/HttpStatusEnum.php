<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Enums;

enum HttpStatusEnum: int
{
    case OK = 200;
    case CREATED = 201;
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case INTERNAL_ERROR = 500;
}
