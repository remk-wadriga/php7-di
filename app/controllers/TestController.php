<?php declare(strict_types = 1);

namespace app\controllers;

use app\ISayHello;
use rkwadriga\filereader\Factory;

class TestController
{
    private ISayHello $helloService;

    public function __construct(ISayHello $helloService)
    {
        $this->helloService = $helloService;
    }

    public function helloAction() : void
    {
        $dir = '/home/rkwadriga/home-projects/php7-di/runtime';
        $fileReader = (new Factory($dir))->getReader('main.yml');
        $fileReader->writeData([
            'var1' => 'Value 1',
            'var2' => 'Value 2',
        ]);
        \DebugHelper::dump($fileReader->readFile());

        //$this->helloService->sayHello('Hello world');
    }
}