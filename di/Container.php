<?php declare(strict_types = 1);

namespace di;

use \ReflectionMethod;

class Container
{
    private array $routing = [];
    private array $container = [];
    private ConfigManager $configManager;

    public function __construct(string $configDir)
    {
        $this->configManager = new ConfigManager($configDir);
        $this->createRouting();
    }

    /**
     * @param string $class
     * @param bool $persistent
     * @return object
     * @throws DiException
     */
    public function getInstance(string $class, bool $persistent = true) : object
    {
        if ($persistent && isset($this->container[$class])) {
            return $this->container[$class];
        }

        if (!class_exists($class) && !interface_exists($class)) {
            throw new DiException("Class {$class} is not found", DiException::CODE_CLASS_NOT_FOUND);
        }

        // Find implementation class for interface
        if (interface_exists($class)) {
            $interface = $class;
            if (isset($this->routing[$interface])) {
                // Get from routing (config + runtime cache)
                $class = $this->routing[$interface];
            } else {
                // Try to find in project directories
                $class = $this->findFirstImplementClass($interface);
            }
            if ($class === null || !class_exists($class)) {
                throw new DiException("Can not find implementation for interface {$interface}", DiException::CODE_IMPLEMENTATION_NOT_FOUND);
            }
            // Add class to routing like a runtime cache
            $this->routing[$interface] = $class;
        }

        try {
            // Create constructor params array
            $params = [];
            if (method_exists($class, '__construct')) {
                $reflect = new ReflectionMethod($class, '__construct');
                foreach ($reflect->getParameters() as $param) {
                    if ($param->getClass() === null) {
                        break;
                    }
                    $params[] = $this->getInstance($param->getClass()->name);
                }
            }

            // Create new class instance
            $reflect = new \ReflectionClass($class);
            $instance = $reflect->newInstanceArgs($params);
            if ($persistent) {
                $this->container[$class] = $instance;
            }
            return $instance;
        } catch (\ReflectionException $e) {
            throw new DiException($e->getMessage(), DiException::CODE_REFLECTION_EXCEPTION, $e);
        }
    }

    public function getConfigManager() : ConfigManager
    {
        return $this->configManager;
    }

    private function createRouting(?array $config = null) : void
    {
        if ($config === null) {
            $config = $this->configManager->getConfiguration();
        }
        foreach ($config as $name => $value) {
            if (is_array($value)) {
                $this->createRouting($value);
            } elseif (is_string($name) && is_string($value) && strpos($name, '\\') !== false && strpos($value, '\\') !== false) {
                $this->routing[$name] = $value;
            }
        }
    }

    private function findFirstImplementClass(string $interface, ?string $dir = null) : ?string
    {
        static $baseDir;
        static $configDir;
        static $currentDir;
        if ($dir === null) {
            $baseDir = $this->configManager->getBaseDir();
            $configDir = basename($this->configManager->getConfigDir());
            $currentDir = $this->configManager->getDirname();
            $dir = $baseDir;
        }

        // Only directories (but not config or current directory) and php-files
        $files = array_filter(scandir($dir), function ($file) use ($configDir, $currentDir) {
            if (in_array($file, ['.', '..', $configDir, $currentDir])) {
                return null;
            }
            return strpos($file, '.') === false || preg_match('/^.+\.php$/', $file) ? $file : null;
        });

        // Recursively search first found implementation class in all directories
        $implementClass = null;
        foreach ($files as $file) {
            $file = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($file)) {
                $implementClass = $this->findFirstImplementClass($interface, $file);
                if ($implementClass !== null) {
                    break;
                }
            } else {
                // Create class name by file path (all directories must have the same names like namespaces)
                $className = str_replace($baseDir . DIRECTORY_SEPARATOR, '', $file);
                $className = str_replace(DIRECTORY_SEPARATOR, '\\', $className);
                $className = str_replace('.php', '', $className);

                // Check is this class and is it implements needed interface
                try {
                    if (interface_exists($className)) {
                        continue;
                    }
                    if (class_exists($className) && in_array($interface, class_implements($className))) {
                        $implementClass = $className;
                        break;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return $implementClass;
    }
}