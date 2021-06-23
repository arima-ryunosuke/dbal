<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin;

use Doctrine\DBAL\Plugin\Pluggable;
use LogicException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class PluggableTest extends TestCase
{
    function test_intercept()
    {
        $pluggable = new Plugged();
        self::assertEquals('initialABC', $pluggable->interceptMethod('initial'));
    }

    function test_interrupt()
    {
        $pluggable = new Plugged();
        self::assertEquals('initialB', $pluggable->interruptMethod('initial'));
    }
}

trait PluginA
{
    function interceptMethodByPluginAForCalled($arg)
    {
        return ['arg' => $arg . 'A'];
    }

    function interruptMethodByPluginAForCalled($arg)
    {
        // through
        return null;
    }
}

trait PluginB
{
    function interceptMethodByPluginBForCalled($arg)
    {
        return ['arg' => $arg . 'B'];
    }

    function interruptMethodByPluginBForCalled($arg)
    {
        $arg .= 'B';
        return $arg;
    }
}

trait PluginC
{
    function interceptMethodByPluginCForCalled($arg)
    {
        return ['arg' => $arg . 'C'];
    }

    function interruptMethodByPluginCForCalled($arg)
    {
        // not call
        throw new LogicException();
    }
}

trait PluginX
{
    function interceptMethodByPluginXForCalled($dummyarg)
    {
        Assert::fail('never called');
    }

    function interruptMethodByPluginXForCalled($dummyarg)
    {
        Assert::fail('never called');
    }
}

class Plugged
{
    use Pluggable;
    use PluginA;
    use PluginB;
    use PluginC;
    use PluginX;

    function filterPlugins($plugins)
    {
        $plugins = array_map(fn($plugin) => explode('\\', $plugin)[5] ?? null, $plugins);
        $plugins = array_filter($plugins, fn($plugin) => $plugin !== null);
        return $plugins;
    }

    function interceptMethod($arg)
    {
        extract($this->intercept(get_defined_vars(), 'NotCalled', 'method'));
        extract($this->intercept(get_defined_vars(), 'Called', 'method'));
        return $arg;
    }

    function interruptMethod($arg)
    {
        $this->interrupt(get_defined_vars(), 'NotCalled', 'method');
        return $this->interrupt(get_defined_vars(), 'Called', 'method');
    }
}
