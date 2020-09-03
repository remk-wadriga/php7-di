<?php declare(strict_types = 1);

namespace di;

class Autoloader
{
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function register() : void
    {
        spl_autoload_register(function ($name) {
            $this->loadClass($name);
        });
    }

    public function loadClass(string $name) : void
    {
        $classPath = $this->basePath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
        if (!file_exists($classPath)) {
            throw new \ErrorException("Can not load class {$name}: file {$classPath} is not found");
        }
        require_once $classPath;
    }
}