<?php

namespace Framework\Util;

/**
 * Enum för ofta använda HTTP status-koder.
 */
enum StatusCode: int
{

    case OK = 200;

    case NotFound = 404;
    case BadRequest = 400;
    case Forbidden = 403;

}