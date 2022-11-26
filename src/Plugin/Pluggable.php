<?php

namespace Doctrine\DBAL\Plugin;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

trait Pluggable
{
    /**
     * @codeCoverageIgnore
     */
    private static function generateAll(): void
    {
        $namespace        = __NAMESPACE__;
        $plugin_directory = __DIR__;
        $target_directory = __DIR__ . '/All';

        $rdi = new RecursiveDirectoryIterator($target_directory, FilesystemIterator::SKIP_DOTS);
        $rii = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($rii as $current) {
            unlink($current->getRealPath());
        }

        $rdi = new RecursiveDirectoryIterator($plugin_directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_SELF);
        $rii = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::LEAVES_ONLY);

        $plugins = [];
        /** @var RecursiveDirectoryIterator $plugin */
        foreach ($rii as $plugin) {
            $subpath = preg_replace('#\.php$#', '', $plugin->getSubPathname());

            $class_name = implode('\\', array_slice(explode(DIRECTORY_SEPARATOR, $subpath), 1));
            $short_name = strtr($subpath, [DIRECTORY_SEPARATOR => '\\']);
            $trait_name = "$namespace\\$short_name";

            if (trait_exists($trait_name)) {
                $plugins[$class_name][] = $trait_name;
            }
        }

        foreach ($plugins as $class_name => $traits) {
            if ($class_name === '') {
                continue;
            }

            $parts     = explode('\\', $class_name);
            $name      = array_pop($parts);
            $subspace  = implode('\\', $parts);
            $mainspace = rtrim("$namespace\\All\\$subspace", '\\');
            $use       = "    use \\" . __TRAIT__ . ";";
            $uses      = implode("\n", array_map(fn($trait) => "    use \\$trait;", $traits));
            $filename  = strtr("$target_directory/$subspace/$name.php", ['\\' => DIRECTORY_SEPARATOR]);

            $contents = <<<ALL
            <?php
            
            namespace $mainspace;
            
            /**
             * @used-by \\Doctrine\\DBAL\\$subspace\\$name
             */
            trait $name
            {
            $use
            $uses
            }
            
            ALL;

            @mkdir(dirname($filename), umask(), true);
            file_put_contents($filename, $contents);
        }
    }

    private function getHookMethods(string $type, string $methodName): array
    {
        static $cache = [];

        $class = get_class($this);

        $methods = $cache[$class]["methods"] ??= (function () {
            //var_dump("called methods of " . get_class($this));
            $methods = [];
            foreach ((new ReflectionClass($this))->getMethods() as $method) {
                $attrs = $method->getAttributes(Hook::class, ReflectionAttribute::IS_INSTANCEOF);
                if (! $attrs) {
                    continue;
                }

                $attr = $attrs[0]->newInstance();

                $methods[$method->name] = [$attr, $method];
            }
            uasort($methods, fn($a, $b) => $b[0]->priority <=> $a[0]->priority);
            return $methods;
        })();

        return $cache[$class]["$type-$methodName"] ??= (function ($methods) use ($type, $methodName) {
            //var_dump("called $type-$methodName of " . get_class($this));
            return array_filter($methods, function ($v) use ($type, $methodName) {
                [$attr, $method] = $v;
                return is_a($attr, $type, true) && preg_match("#^{$methodName}By#i", $method->name);
            });
        })($methods);
    }

    private function rearguments(ReflectionMethod $refmethod, array $namedArguments): ?array
    {
        $parameters = $refmethod->getParameters();

        $positionArgs = [];
        foreach ($parameters as $parameter) {
            if (
                array_key_exists($n = $parameter->getPosition(), $namedArguments) ||
                array_key_exists($n = $parameter->getName(), $namedArguments)
            ) {
                $positionArgs[] = $namedArguments[$n];
            }
        }

        // arguments no matches
        if ($refmethod->getNumberOfRequiredParameters() !== count($positionArgs)) {
            return null;
        }

        return $positionArgs;
    }

    protected function intercept(array $arguments = [], ?string $for = null, ?string $methodName = null)
    {
        $methodName ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];

        $result = [];
        foreach ($this->getHookMethods(Intercept::class, $methodName) as [$attr, $refmethod]) {
            if ($for !== null && ! preg_match("#For$for$#i", $refmethod->getName())) {
                continue;
            }
            $positionalArguments = $this->rearguments($refmethod, $result + $arguments);
            if ($positionalArguments !== null) {
                $this->reportCallHistory($methodName, $refmethod);
                $result = $refmethod->invoke($this, ...$positionalArguments) + $result;
            }
        }

        return $result;
    }

    protected function interrupt(array $arguments = [], ?string $for = null, ?string $methodName = null)
    {
        $methodName ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];

        foreach ($this->getHookMethods(Interrupt::class, $methodName) as [$attr, $refmethod]) {
            if ($for !== null && ! preg_match("#For$for$#i", $refmethod->getName())) {
                continue;
            }
            $positionalArguments = $this->rearguments($refmethod, $arguments);
            if ($positionalArguments !== null) {
                $this->reportCallHistory($methodName, $refmethod);
                $result = $refmethod->invoke($this, ...$positionalArguments);
                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }

    private function reportCallHistory(string $methodName, ReflectionMethod $refmethod): void
    {
        if (isset($GLOBALS['__callHistories'])) {
            $namespace        = __NAMESPACE__;
            $plugin_directory = __DIR__;

            if (! $GLOBALS['__callHistories']) {
                $rdi = new RecursiveDirectoryIterator($plugin_directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_SELF);
                $rii = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::LEAVES_ONLY);

                /** @var RecursiveDirectoryIterator $plugin */
                foreach ($rii as $plugin) {
                    $subpath = preg_replace('#\.php$#', '', $plugin->getSubPathname());

                    if (str_starts_with($subpath, 'All')) {
                        continue;
                    }

                    $short_name = strtr($subpath, [DIRECTORY_SEPARATOR => '\\']);
                    $trait_name = "$namespace\\$short_name";

                    if (trait_exists($trait_name) && $trait_name !== __TRAIT__) {
                        foreach ((new ReflectionClass($trait_name))->getMethods() as $method) {
                            if ($method->getAttributes(Hook::class, ReflectionAttribute::IS_INSTANCEOF)) {
                                $GLOBALS['__callHistories'][$method->class . '::' . $method->name] = [];
                            }
                        }
                    }
                }
            }

            $subpath    = preg_replace('#((^' . preg_quote($plugin_directory . DIRECTORY_SEPARATOR) . ')|(\.php)$)#', '', $refmethod->getFileName());
            $short_name = strtr($subpath, [DIRECTORY_SEPARATOR => '\\']);
            $trait_name = "$namespace\\$short_name";

            if (trait_exists($trait_name)) {
                $callee = "{$trait_name}::{$refmethod->name}";
                $caller = "{$refmethod->class}::{$methodName}";

                $GLOBALS['__callHistories'][$callee][$caller] ??= 0;
                $GLOBALS['__callHistories'][$callee][$caller]++;
            }
        }
    }
}
