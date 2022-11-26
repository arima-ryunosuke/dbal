<?php

namespace Doctrine\DBAL\Plugin\Trigger\Platforms;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Trigger;

/**
 * @codeCoverageIgnore
 */
trait AbstractPlatform
{
    public function supportsTriggers(): bool
    {
        return false;
    }

    public function getCreateTriggerSQL(Trigger $trigger): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    public function getDropTriggerSQL(string $trigger, string $table = ''): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    public function getAlterTriggerSQL(Trigger $trigger): array
    {
        return [
            $this->getDropTriggerSQL($trigger->getQuotedName($this), $trigger->getTableName()),
            $this->getCreateTriggerSQL($trigger),
        ];
    }
}
