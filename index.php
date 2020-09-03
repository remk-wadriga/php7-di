<?php

require './vendor/autoload.php';
require './DebugHelper.php';

$autoloader = new rkwadiga\simpledi\Autoloader(__DIR__);
$autoloader->register();

$app = new app\TestApp('./config');
$app->run();