<?php declare(strict_types=1);

namespace app\services;

class LoggerService
{
    public string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }
}