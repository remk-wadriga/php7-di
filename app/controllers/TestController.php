<?php
declare(strict_types = 1);

namespace app\controllers;

use app\ISayHello;

class TestController
{
    public ISayHello $helloService;

    public function __construct(ISayHello $helloService)
    {
        $this->helloService = $helloService;
    }

    public function helloAction()
    {
        $this->helloService->sayHello('Hello world');
    }
}