<?php
declare(strict_types = 1);

namespace di;

class ConfigManager
{
    private string $baseDIr;
    private string $configFilesDir;
    private ?string $dirname = null;
    private array $allowedExtensions = ['yml', 'yaml'];

    public function __construct(string $configFilesDir = 'config')
    {
        if (!is_dir($configFilesDir)) {
            $configFilesDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . $configFilesDir;
        }
        if (!is_dir($configFilesDir)) {
            throw new DiException("Config directory {$configFilesDir} does not exist", DiException::CODE_INVALID_CONFIGURATION_DIR);
        }
        $this->baseDIr = dirname(realpath($configFilesDir));
        $this->configFilesDir = $configFilesDir;
    }

    public function getConfiguration():array
    {
        $files = array_filter(scandir($this->configFilesDir), function ($file) {
            if (in_array($file, ['.', '..'])) {
                return null;
            }
            $pattern = sprintf('/^.+\.%s$/', implode('|', $this->allowedExtensions));
            return preg_match($pattern, $file) ? $file : null;
        });

        $config = [];

        foreach ($files as $file) {
            $file = $this->configFilesDir . DIRECTORY_SEPARATOR . $file;
            try {
                $config = array_merge($config, (array)yaml_parse_file($file));
            } catch (\Exception $e) {
                throw new DiException(sprintf('Invalid config file %s: %s', $file, $e->getMessage()), DiException::CODE_INVALID_CONFIGURATION_FILE, $e);
            }
        }

        return $config;
    }

    public function setBaseDir(string $baseDir):void
    {
        if (!is_dir($baseDir)) {
            throw new DiException("Invalid base dir: {$baseDir}", DiException::CODE_INVALID_PARAM);
        }
        $this->baseDIr = $baseDir;
    }
    public function getBaseDir():string
    {
        return  $this->baseDIr;
    }

    public function getDirname():string
    {
        if ($this->dirname !== null) {
            return $this->dirname;
        }
        return $this->dirname = basename(__DIR__);
    }

    public function getConfigDir():string
    {
        return $this->configFilesDir;
    }
}