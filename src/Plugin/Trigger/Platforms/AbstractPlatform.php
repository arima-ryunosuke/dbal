<?php

namespace Doctrine\DBAL\Plugin\Trigger\Platforms;

use Doctrine\DBAL\Platforms\Exception\NotSupported;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\Trigger;

/**
 * @codeCoverageIgnore
 */
trait AbstractPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getAlterSchemaSQL()
     */
    protected function interceptGetAlterSchemaSQLByTrigger(array $sql, SchemaDiff $diff): array
    {
        foreach ($diff->createdTriggers as $trigger) {
            $sql[] = $this->getCreateTriggerSQL($trigger);
        }

        foreach ($diff->droppedTriggers as $trigger) {
            $sql[] = $this->getDropTriggerSQL($trigger->getLocalName(), $trigger->getTableName());
        }

        foreach ($diff->alteredTriggers as $n => $trigger) {
            $alteredSqls       = $this->getAlterTriggerSQL($trigger);
            $key               = array_key_first($alteredSqls);
            $alteredSqls[$key] = $this->buildDiffComment($diff->triggerDiffs[$n]) . $alteredSqls[$key];

            $sql = array_merge($sql, $alteredSqls);
        }

        return ['sql' => $sql];
    }

    public function supportsTriggers(): bool
    {
        return false;
    }

    public function supportsInlineTriggers(): bool
    {
        return $this->supportsTriggers();
    }

    public function buildTriggerStatement(string|array $statements): string
    {
        $statements = (array) $statements;
        return "BEGIN " . implode('; ', $statements) . "; END";
    }

    public function getCreateTriggerSQL(Trigger $trigger): string
    {
        throw NotSupported::new(__METHOD__);
    }

    public function getDropTriggerSQL(string $trigger, string $table = ''): string
    {
        throw NotSupported::new(__METHOD__);
    }

    public function getAlterTriggerSQL(Trigger $trigger): array
    {
        return [
            $this->getDropTriggerSQL($trigger->getLocalName(), $trigger->getTableName()),
            $this->getCreateTriggerSQL($trigger),
        ];
    }
}
