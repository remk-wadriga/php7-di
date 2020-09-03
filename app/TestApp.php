<?php declare(strict_types = 1);

namespace app;

use app\controllers\TestController;
use rkwadiga\simpledi\Container;

class TestApp
{
    protected Container $container;

    public function __construct(string $configDir)
    {
        $this->container = new Container($configDir);
    }

    public function run() : void
    {
        /** @var TestController $controller */
        $controller = $this->container->getInstance(TestController::class);
        $controller->helloAction();
    }
}