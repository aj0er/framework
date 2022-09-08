<?php

namespace Framework\Util;

/**
 * Enum för ofta använda HTTP status-koder.
 */
enum StatusCode: int
{

    case OK = 200;
    case Created = 201;
    case NoContent = 204;

    case Found = 301;

    case BadRequest = 400;
    case Unauthorized = 401;
    case Forbidden = 403;
    case NotFound = 404;
    case TooManyRequests = 429;

    // :)
    case Teapot = 418;

    case InternalServerError = 500;
    
}