<?php
declare(strict_types = 1);

namespace app;

interface ISayHello
{
    public function sayHello(string $string):void;
}