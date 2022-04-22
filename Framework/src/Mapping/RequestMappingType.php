<?php

namespace Framework\Mapping;

/**
 * Enum med de typer som förfrågningar kan mappas som.
 */
enum RequestMappingType
{
    case FORM; // Datan fås från URL-encodade värden.
    case JSON_BODY; // Datan fås från ett JSON-objekt i förfrågans body.
}