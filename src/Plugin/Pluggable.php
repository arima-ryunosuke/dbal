<?php

namespace Doctrine\DBAL\Plugin;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
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

    private function filterPlugins(array $plugins): array
    {
        $plugins = array_map(fn($plugin) => explode("\\", $plugin)[3] ?? null, $plugins);
        $plugins = array_filter($plugins, fn($name, $plugin) => $name !== null && str_starts_with($plugin, __NAMESPACE__), ARRAY_FILTER_USE_BOTH);

        return $plugins;
    }

    private function getAllTrait(string $class): array
    {
        $traits = class_uses($class);

        do {
            $count = count($traits);
            foreach ($traits as $trait) {
                $traits += class_uses($trait);
            }
        } while ($count !== count($traits));

        return $traits;
    }

    private function getPluginMethods(string $type, string $methodName): array
    {
        $cacher = new class { static array $cache = []; };
        $class  = get_class($this);

        $plugins = ($cacher::$cache)[$class]["plugins"] ??= (function () use ($class) {
            //var_dump("called plugins of " . get_class($this));
            $plugins = [];
            foreach (array_merge([$class], class_parents($this)) as $class) {
                $plugins[$class] = $this->filterPlugins($this->getAllTrait($class));
            }
            return $plugins;
        })();
        $methods = ($cacher::$cache)[$class]["methods"] ??= (function ($plugins) {
            //var_dump("called methods of " . get_class($this));
            $methods = [];
            foreach ($plugins as $class => $traits) {
                foreach ($traits as $trait => $plugin) {
                    foreach ((new ReflectionClass($trait))->getMethods() as $method) {
                        // hack for ReflectionMethod custom property
                        $refmethod = new class($class, $method->name) extends ReflectionMethod {
                            public ReflectionMethod $getUsingMethod;
                        };

                        $refmethod->getUsingMethod      = $method;
                        $methods[$method->name][$class] = $refmethod;
                    }
                }
            }
            return $methods;
        })($plugins);

        return ($cacher::$cache)[$class]["$type-$methodName"] ??= (function ($plugins, $methods) use ($type, $methodName) {
            //var_dump("called $type-$methodName of " . get_class($this));
            $suffixes = [];
            foreach ($plugins as $traits) {
                foreach ($traits as $plugin) {
                    $suffixes[] = $plugin;
                }
            }
            $pattern = "#^{$type}{$methodName}By(" . implode('|', $suffixes) . ")#i";
            return array_intersect_key($methods, array_flip(preg_grep($pattern, array_keys($methods))));
        })($plugins, $methods);
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
        foreach ($this->getPluginMethods('intercept', $methodName) as $refmethods) {
            foreach ($refmethods as $refmethod) {
                if ($for !== null && ! preg_match("#For$for$#i", $refmethod->getName())) {
                    continue;
                }
                $positionalArguments = $this->rearguments($refmethod, $result + $arguments);
                if ($positionalArguments !== null) {
                    $this->reportCallHistory($methodName, $refmethod);
                    $result = $refmethod->invoke($this, ...$positionalArguments) + $result;
                }
            }
        }

        return $result;
    }

    protected function interrupt(array $arguments = [], ?string $for = null, ?string $methodName = null)
    {
        $methodName ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];

        foreach ($this->getPluginMethods('interrupt', $methodName) as $refmethods) {
            foreach ($refmethods as $refmethod) {
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
        }

        return null;
    }

    private function reportCallHistory(string $methodName, ReflectionMethod $refmethod): void
    {
        if (isset($GLOBALS['__callHistories'])) {
            if (! $GLOBALS['__callHistories']) {
                $namespace        = __NAMESPACE__;
                $plugin_directory = __DIR__;

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
                            if (preg_match('#^(intercept|interrupt)#', $method->name)) {
                                $GLOBALS['__callHistories'][$method->class . '::' . $method->name] = [];
                            }
                        }
                    }
                }
            }

            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $callee = "{$refmethod->getUsingMethod->class}::{$refmethod->name}";
            $caller = "{$refmethod->class}::{$methodName}";

            $GLOBALS['__callHistories'][$callee][$caller] ??= 0;
            $GLOBALS['__callHistories'][$callee][$caller]++;
        }
    }
}
