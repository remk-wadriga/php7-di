# Simple DI container

## Installation
```bash
$ composer install rkwadiga/simple-di
```

## Using
Create config file (for example: config/main.yml) and set implementation class for your interfaces:
```yml
services:
  app\ISayHello: app\services\ConsoleHelloService
```
Create the new container instance:
```php
class MyApp
{
    private Container $container;

    public function __construct()
    {
        $this->container = new Container('./config');
    }
}
```
Put the config directory to Container's constructor: it will find all .yml and .yaml files from it
and find all declarations like "<namespaces>\<interface name>: <namespaces>\<class name>".
If implementation class is not found for some interface - Container will search the first one in config's parent directory.

Get the "singleton" class instance:
```php
    $instance = $this->container->getInstance(MyClass::class);
```
Or new class instance:
```php
    $instance = $this->container->getInstance(MyClass::class, false);
```
The same for interface implementation:
```php
    // Singleton
    $implementation = $this->container->getInstance(MyInterface::class);
    ...
    // New instance
    $implementation = $this->container->getInstance(MyInterface::class, false);
```

All classes that you created using Container's "getInstance" method can have objects as arguments in their constructors:
```php
class MyClass
{
    private MyService1 $service1;
    private MyService2 $service2;

    public function __construct(MyService1 $service1, MyService2 $service2)
    {
        $this->service1 = $service1;
        $this->service2 = $service2;
    }
}
```

The same for interfaces:
```php
class MyClass
{
    private MyInterface1 $service1;
    private MyInterface2 $service2;

    public function __construct(MyInterface1 $service1, MyInterface2 $service2)
    {
        $this->service1 = $service1;
        $this->service2 = $service2;
    }
}
```