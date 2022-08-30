<?php

namespace App\Controllers;

use Framework\Controller\Controller;
use Framework\Controller\Response\ViewResponse;

/**
 * Controller för allt som gäller startsidan och liknande sidor som inte har någon forumlogik.
 */
class IndexController extends Controller
{

    function indexPage(): ViewResponse
    {
        return parent::view("index");
    }

}