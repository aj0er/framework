<?php

namespace App\Entity;

/**
 * Enum för användarroller.
 */
enum Role: string {
    case USER = 'user';
    case ADMIN = 'admin';
}