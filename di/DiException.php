<?php declare(strict_types = 1);

namespace di;

use \Exception;

class DiException extends Exception
{
    const CODE_INVALID_PARAM = 1001;
    const CODE_CLASS_NOT_FOUND = 1004;
    const CODE_REFLECTION_EXCEPTION = 1005;
    const CODE_INVALID_CONFIGURATION_DIR = 1006;
    const CODE_INVALID_CONFIGURATION_FILE = 1007;
    const CODE_IMPLEMENTATION_NOT_FOUND = 1014;

    public string $name = 'Di Exception';
}