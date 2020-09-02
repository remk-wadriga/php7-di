<?php

class DebugHelper
{
    private static $depthLimit = 11;

    public static function dump(...$vars)
    {
        $backtrace = debug_backtrace();
        $wasCalled = '';
        if (isset($backtrace[1]['class'])) {
            $classParts = explode('\\', $backtrace[1]['class']);
            $wasCalled .= end($classParts);
        } elseif (isset($backtrace[1]['file'])) {
            $wasCalled .= $backtrace[1]['file'];
        }
        if (!empty($wasCalled) && isset($backtrace[1]['function'])) {
            $wasCalled .= ':' . $backtrace[1]['function'] . '()';
        }
        if (!empty($wasCalled) && isset($backtrace[0]['line'])) {
            $wasCalled .= ', line ' . $backtrace[0]['line'];
        }
        if (!empty($wasCalled)) {
            $wasCalled = '>> ' . $wasCalled;
        }

        $isConsole = self::isConsole();
        $text = $isConsole ? "\n" : '<div style="background-color:black;color:white;"><br />';
        $text .= $isConsole ? "\e[31m{$wasCalled}\e[0m:\n\n" : "<font color=\"red\">{$wasCalled}</font>:<br /><br />";
        foreach ($vars as $var) {
            $text .= self::dumpVariableRecursively($var, $isConsole);
        }
        echo $text, ($isConsole ? "\n" : '<br /></div>');
        exit();
    }

    public static function trace($showVendorCalls = false)
    {
        self::dump(self::getTrace($showVendorCalls));
    }

    public static function getTrace($showVendorCalls = false)
    {
        $backtrace = debug_backtrace();
        $trace = [];
        if (is_array($backtrace)) {
            // Delete call of current function
            array_shift($backtrace);
            // Reverse the colling list
            $backtrace = array_reverse($backtrace);
            if (!$showVendorCalls) {
                // Delete first colling in "index.php"
                array_shift($backtrace);
                // Remove all framework colling trace
                $backtrace = array_filter($backtrace, function ($item) {
                    if (!isset($item['file']) || !isset($item['line']) || !isset($item['function'])) {
                        return null;
                    }
                    if (strpos($item['file'], '/vendor/yiisoft/yii2') !== false) {
                        return null;
                    }
                    return $item;
                });
            }

            $appPath = Yii::getAlias('@app');
            $i = 1;
            foreach ($backtrace as $item) {
                if (!isset($item['file']) || !isset($item['line']) || !isset($item['function'])) {
                    continue;
                }
                $file = str_replace(["{$appPath}/modules/api/controllers/", "{$appPath}/components", "{$appPath}/models", "{$appPath}/modules/api/abstracts/", "{$appPath}/modules/api/", "{$appPath}/modules/", $appPath, '.php'], '', $item['file']);
                $trace[] = sprintf('%s. %s:%s -> %s()', $i++, $file, $item['line'], $item['function']);
            }
        }
        return $trace;
    }

    public static function getTraceAsString($sep = "\n", $showVendorCalls = false)
    {
        return implode($sep, self::getTrace($showVendorCalls));
    }

    private static function dumpVariableRecursively($var, $isConsole, $prefix = '-', $postfix = '', $text = '', $depth = 0)
    {
        $textPrev = $text;

        $type = gettype($var);
        if ($prefix === '-') {
            $text .= $prefix . ' ';
            $prefix = '';
        }

        if ($depth > self::$depthLimit && in_array($type, ['array', 'object'])) {
            $printed = self::clearPrintedVar(self::printVarRecursively($var, $type, $isConsole));
            return $text . $printed . '...' . $postfix . ($isConsole ? "\n" : '<br />');
        }

        if (in_array($type, ['array', 'object'])) {
            $text .= $type == 'array' ? '[' : self::className($var) . ' {';
            $text .= $isConsole ? "\n" : '<br />';
            $prefixPerv = $prefix;
            $prefix .= $isConsole ? '    ' : '&nbsp;&nbsp;&nbsp&nbsp&nbsp&nbsp&nbsp';

            if ($type == 'object' && $var instanceof \Exception) {
                $var = [
                    'code' => $var->getCode(),
                    'file' => $var->getFile(),
                    'line' => $var->getLine(),
                    'message' => $var->getMessage(),
                    'trace' => explode('#', $var->getTraceAsString()),
                    'previous' => $var->getPrevious(),
                ];
            }

            foreach ($var as $index => $arrayVar) {
                $text .= self::printIndex($index, $isConsole, $prefix) . ': ';
                $text .= self::dumpVariableRecursively($arrayVar, $isConsole, $prefix, ',', $textPrev, $depth + 1);
            }
            if ($isConsole && substr($text, -2) === ",\n") {
                $text = substr($text, 0, -2) . "\n";
            } elseif (substr($text, -7) === ',<br />') {
                $text = substr($text, 0, -7) . '<br />';
            }
            $text .= $prefixPerv . ($isConsole ? "  " : '&nbsp;&nbsp;');
            $text .= $type == 'array' ? ']' : '}';
        } elseif ($type === 'resource') {
            $text .= sprintf('Resource #%s', (int)$var);
        } else {
            $printed = (strpos($var, 'array...') === false && strpos($var, 'array[') === false)
                ? self::printVarRecursively($var, $type, $isConsole)
                : self::clearPrintedVar($var)
            ;
            if (strpos($var, 'array...') !== false) {
                $printed = str_replace('array...', 'array', $printed) . '...';
            }
            $text .= $printed;
        }

        return $text . $postfix . ($isConsole ? "\n" : '<br />');
    }

    private static function printIndex($index, $isConsole, $prefix = '')
    {
        if ($isConsole) {
            $pattern = is_string($index) ? "%s\"\e[31m%s\e[0m\"" : "%s\e[31m%s\e[0m";
        } else {
            $pattern = is_string($index) ? '%s"<font color="red">%s</font>"' : '%s<font color="red">%s</font>';
        }
        return sprintf($pattern, $prefix, $index);
    }

    private static function printVarRecursively($var, $type, $isConsole)
    {
        $pattern = $isConsole ? "\e[32m%s\e[0m" : '<font color="green">%s</font>';
        $stringPattern = $isConsole ? "\"\e[32m%s\e[0m\"" : '"<font color="green">%s</font>"';
        switch ($type) {
            case 'string':
                $pattern = $stringPattern;
                break;
            case 'array':
                $var = self::maxDepthVar($var);
                if (is_array($var)) {
                    $data = [];
                    foreach (self::maxDepthVar($var) as $index => $value) {
                        $value = self::printVarRecursively($value, $type, $isConsole);
                        $data[] = sprintf('%s: %s', self::printIndex($index, $isConsole), $value);
                    }
                    $var = 'array[' . implode(', ', $data) . ']' ;
                }
                break;
            case 'object':
                return self::className($var);
                break;
            case 'NULL':
                $var = 'NULL';
                break;
            case 'boolean':
                $var = $var ? 'TRUE' : 'FALSE';
                break;
            default:
                break;
        }
        return sprintf($pattern, $var);
    }

    private static function maxDepthVar($arg)
    {
        switch (gettype($arg)) {
            case 'object':
                $arg = self::className($arg);
                break;
            case 'array':
                foreach ($arg as $argIndex => $argValue) {
                    switch (gettype($argValue)) {
                        case 'object':
                            $arg[$argIndex] = self::className($argValue);
                            break;
                        case 'array':
                            $arg[$argIndex] = 'array...';
                            break;
                    }
                }
                if (empty($arg)) {
                    $arg = 'array...';
                }
                break;
        }
        return $arg;
    }

    private static function clearPrintedVar($printed)
    {
        $printed = str_replace('array[', '[', $printed);
        return str_replace('array...', 'array', $printed);
    }

    private static function className($object)
    {
        $classParts = explode('\\', get_class($object));
        return end($classParts);
    }

    private static function isConsole()
    {
        return php_sapi_name() == 'cli';
    }
}