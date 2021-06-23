<?php

namespace Doctrine\DBAL\Tests\Functional\Plugin;

use Doctrine\DBAL\Plugin\Intercept;
use Doctrine\DBAL\Plugin\Interrupt;
use Doctrine\DBAL\Plugin\Pluggable;
use LogicException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class PluggableTest extends TestCase
{
    function test_intercept()
    {
        $pluggable = new Plugged();
        self::assertEquals('initialBCA', $pluggable->interceptMethod('initial'));
    }

    function test_interrupt()
    {
        $pluggable = new Plugged();
        self::assertEquals('initialB', $pluggable->interruptMethod('initial'));
    }
}

trait PluginA
{
    #[Intercept]
    function ceptByPluginAForCalled($arg)
    {
        return ['arg' => $arg . 'A'];
    }

    #[Interrupt]
    function ruptByPluginAForCalled($arg)
    {
        $arg .= 'A';
        return $arg;
    }
}

trait PluginB
{
    #[Intercept(priority: 9)]
    function ceptByPluginBForCalled($arg)
    {
        return ['arg' => $arg . 'B'];
    }

    #[Interrupt(priority: 9)]
    function ruptByPluginBForCalled($arg)
    {
        $arg .= 'B';
        return $arg;
    }
}

trait PluginC
{
    #[Intercept(priority: 5)]
    function ceptByPluginCForCalled($arg)
    {
        return ['arg' => $arg . 'C'];
    }

    #[Interrupt]
    function ruptByPluginCForCalled($arg)
    {
        // not call
        throw new LogicException();
    }
}

trait PluginX
{
    #[Intercept]
    function ceptByPluginXForCalled($dummyarg)
    {
        Assert::fail('never called');
    }

    #[Interrupt]
    function ruptByPluginXForCalled($dummyarg)
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

    function interceptMethod($arg)
    {
        extract($this->intercept(get_defined_vars(), 'NotCalled', 'cept'));
        extract($this->intercept(get_defined_vars(), 'Called', 'cept'));
        return $arg;
    }

    function interruptMethod($arg)
    {
        $this->interrupt(get_defined_vars(), 'NotCalled', 'rupt');
        return $this->interrupt(get_defined_vars(), 'Called', 'rupt');
    }
}
