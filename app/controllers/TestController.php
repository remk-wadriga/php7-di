<?php declare(strict_types = 1);

namespace app\controllers;

use app\ISayHello;

class TestController
{
    private ISayHello $helloService;

    public function __construct(ISayHello $helloService)
    {
        $this->helloService = $helloService;
    }

    public function helloAction() : void
    {
        $this->helloService->sayHello('Hello world');
    }
}