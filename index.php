<?php

require './di/Autoloader.php';
require './DebugHelper.php';

use di\Autoloader;

$autoloader = new Autoloader(__DIR__);
$autoloader->register();

use app\TestApp;

$app = new TestApp('./config');
$app->run();