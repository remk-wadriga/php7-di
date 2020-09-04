<?php declare(strict_types = 1);

namespace app\services;

use app\helpers\IOHelper;
use app\ISayHello;

class ConsoleHelloService extends AbstractService implements ISayHello
{
    public IOHelper $ioHelper;
    public string $helloText;

    public function __construct(IOHelper $helper, string $helloText)
    {
        $this->ioHelper = $helper;
        $this->helloText = $helloText;
    }

    public function sayHello(string $string) : void
    {
        $this->ioHelper->cl($string);
    }
}