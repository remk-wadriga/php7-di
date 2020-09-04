<?php declare(strict_types = 1);

namespace app\controllers;

use app\ISayHello;
use app\services\LoggerService;

class TestController
{
    private ISayHello $helloService;
    private LoggerService $logger;

    public function __construct(ISayHello $helloService, LoggerService $logger)
    {
        $this->helloService = $helloService;
        $this->logger = $logger;
    }

    public function helloAction() : void
    {
        $this->helloService->sayHello('Hello world');
    }
}