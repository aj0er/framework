<?php

namespace Simple;

require_once "vendor/autoload.php";

use Framework\Main;

$main = new Main(new App());
$main->handle();