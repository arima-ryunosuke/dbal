<?php

namespace Doctrine\DBAL\Plugin\Trigger\Platforms;

use Doctrine\DBAL\Schema\Trigger;

/**
 * @codeCoverageIgnore this is implemented to broaden one's horizons
 */
trait PostgreSQLPlatform
{
    public function supportsTriggers(): bool
    {
        return true;
    }

    public function supportsInlineTriggers(): bool
    {
        return false;
    }

    public function buildTriggerStatement(string|array $statements): string
    {
        $statements = (array) $statements;
        assert(count($statements) === 1);
        return "EXECUTE FUNCTION " . reset($statements);
    }

    public function getCreateTriggerSQL(Trigger $trigger): string
    {
        $triggerName = $trigger->getLocalName();
        $tableName   = $trigger->getTableName();
        $statement   = $trigger->getStatement();
        $options     = $trigger->getOptions();
        $timing      = $options['timing'] ?? null;
        $event       = $options['event'] ?? null;
        $foreach     = $options['orientation'] ?? 'ROW';

        return "CREATE TRIGGER $triggerName $timing $event ON $tableName FOR EACH $foreach $statement";
    }

    public function getDropTriggerSQL(string $trigger, string $table = ''): string
    {
        // IF EXISTS is required because Trigger dropped with drop table collateral damage
        return "DROP TRIGGER IF EXISTS $trigger ON $table";
    }
}
