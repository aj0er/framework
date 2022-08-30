<?php

namespace Framework\Util;

/**
 * Enum för ofta använda färgkoder i konsolen.
 */
enum ConsoleColors: string
{
    case GREEN = "\x1b[32m";
    case RED = "\x1b[31m";
    case YELLOW = "\x1b[33m";
    case BLUE = "\x1b[34m";
    case RESET = "\033[0m";
}