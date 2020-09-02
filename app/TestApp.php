<?php
declare(strict_types = 1);

namespace app;

use app\controllers\TestController;
use di\Container;

class TestApp
{
    protected Container $container;

    public function __construct()
    {
        $this->container = new Container();
    }

    public function run()
    {
        /** @var TestController $controller */
        $controller = $this->container->getInstance(TestController::class);
        $controller->helloAction();
    }
}