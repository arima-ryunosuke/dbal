<?php

namespace Doctrine\DBAL\Plugin\Trigger\Schema;

use Doctrine\DBAL\Plugin\Trigger\Schema\Exception\TriggerAlreadyExists;
use Doctrine\DBAL\Plugin\Trigger\Schema\Exception\TriggerDoesNotExist;
use Doctrine\DBAL\Schema\Trigger;

trait Schema
{
    /** @var Trigger[] */
    protected array $_triggers = [];

    /**
     * @used-by \Doctrine\DBAL\Schema\Schema::__clone()
     */
    protected function intercept__CloneByTrigger(): array
    {
        foreach ($this->_triggers as $k => $trigger) {
            $this->_triggers[$k] = clone $trigger;
        }

        return [];
    }

    /** @return Trigger[] */
    public function getTriggers(): array
    {
        return $this->_triggers;
    }

    public function getTrigger(string $triggerName): Trigger
    {
        $name = $this->getFullQualifiedAssetName($triggerName);
        if (! isset($this->_triggers[$name])) {
            throw TriggerDoesNotExist::new($name);
        }

        return $this->_triggers[$name];
    }

    public function hasTrigger(string $triggerName): bool
    {
        $name = $this->getFullQualifiedAssetName($triggerName);

        return isset($this->_triggers[$name]);
    }

    public function addTrigger(Trigger $trigger): self
    {
        $triggerName = $this->normalizeName($trigger);

        if (isset($this->_triggers[$triggerName])) {
            throw TriggerAlreadyExists::new($triggerName);
        }

        $this->_triggers[$triggerName] = $trigger;

        $this->_createNamespaceBy($trigger);

        return $this;
    }

    public function dropTrigger(string $triggerName): self
    {
        $name = $this->getFullQualifiedAssetName($triggerName);
        $this->getTrigger($name);
        unset($this->_triggers[$name]);

        return $this;
    }
}
