<?php declare(strict_types = 1);

namespace di;

class ConfigManager
{
    private string $baseDIr;
    private string $configDir;
    private ?string $dirname = null;
    private array $allowedExtensions = ['yml', 'yaml'];

    public function __construct(string $configDir)
    {
        if (!is_dir($configDir)) {
            throw new DiException("Config directory {$configDir} does not exist", DiException::CODE_INVALID_CONFIGURATION_DIR);
        }
        $this->baseDIr = dirname(realpath($configDir));
        $this->configDir = $configDir;
        $this->dirname = basename(__DIR__);
    }

    public function getConfiguration() : array
    {
        $files = array_filter(scandir($this->configDir), function ($file) {
            if (in_array($file, ['.', '..'])) {
                return null;
            }
            $pattern = sprintf('/^.+\.%s$/', implode('|', $this->allowedExtensions));
            return preg_match($pattern, $file) ? $file : null;
        });

        $config = [];

        foreach ($files as $file) {
            $file = $this->configDir . DIRECTORY_SEPARATOR . $file;
            try {
                $config = array_merge($config, (array)yaml_parse_file($file));
            } catch (\Exception $e) {
                throw new DiException(sprintf('Invalid config file %s: %s', $file, $e->getMessage()), DiException::CODE_INVALID_CONFIGURATION_FILE, $e);
            }
        }

        return $config;
    }

    public function getBaseDir() : string
    {
        return $this->baseDIr;
    }

    public function getDirname() : string
    {
        return $this->dirname;
    }

    public function getConfigDir() : string
    {
        return $this->configDir;
    }
}