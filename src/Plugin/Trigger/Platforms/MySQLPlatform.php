<?php

namespace Doctrine\DBAL\Plugin\Trigger\Platforms;

use Doctrine\DBAL\Schema\Trigger;

trait MySQLPlatform
{
    public function supportsTriggers(): bool
    {
        return true;
    }

    public function getCreateTriggerSQL(Trigger $trigger): string
    {
        $triggerName = $trigger->getQuotedName($this);
        $tableName   = $trigger->getTableName();
        $statement   = $trigger->getStatement();
        $options     = $trigger->getOptions();
        $timing      = $options['timing'] ?? null;
        $event       = $options['event'] ?? null;
        $definer     = $this->_quoteUserHostname($options['definer'] ?? '');
        $foreach     = 'ROW'; // mysql is not supported "FOR EACH STATEMENT"

        return "CREATE " . ($definer ? "DEFINER=$definer " : "") . "TRIGGER $triggerName $timing $event ON $tableName FOR EACH $foreach $statement";
    }

    public function getDropTriggerSQL(string $trigger, string $table = ''): string
    {
        // IF EXISTS is required because Trigger dropped with drop table collateral damage
        return "DROP TRIGGER IF EXISTS $trigger";
    }

    public function getAlterTriggerSQL(Trigger $trigger): array
    {
        return [
            "LOCK TABLES {$trigger->getTableName()} WRITE",
            $this->getDropTriggerSQL($trigger->getQuotedName($this), $trigger->getTableName()),
            $this->getCreateTriggerSQL($trigger),
            "UNLOCK TABLES",
        ];
    }
}
