<?php

namespace Doctrine\DBAL\Plugin\Trigger\Schema;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Trigger;

trait SchemaDiff
{
    /** @var Trigger[] All created trigger */
    public $createdTriggers = [];

    /** @var Trigger[] All dropped trigger */
    public $droppedTriggers = [];

    /** @var Trigger[] All altered trigger */
    public $alteredTriggers = [];

    /** @var array All altered trigger's diff */
    public $triggerDiffs = [];

    /**
     * @used-by \Doctrine\DBAL\Schema\SchemaDiff::isEmpty()
     */
    protected function interruptIsEmptyByTrigger()
    {
        if (count($this->createdTriggers) || count($this->droppedTriggers) || count($this->alteredTriggers)) {
            return false;
        }
    }

    /**
     * @used-by \Doctrine\DBAL\Schema\SchemaDiff::_toSql()
     */
    protected function intercept_ToSqlByTrigger($sql, AbstractPlatform $platform, $saveMode)
    {
        foreach ($this->createdTriggers as $trigger) {
            $sql[] = $platform->getCreateTriggerSQL($trigger);
        }

        if (! $saveMode) {
            foreach ($this->droppedTriggers as $trigger) {
                $sql[] = $platform->getDropTriggerSQL($trigger->getQuotedName($platform), $trigger->getTableName());
            }
        }

        foreach ($this->alteredTriggers as $n => $trigger) {
            $alteredSqls       = $platform->getAlterTriggerSQL($trigger);
            $key               = array_key_first($alteredSqls);
            $alteredSqls[$key] = $platform->buildDiffComment($this->triggerDiffs[$n]) . $alteredSqls[$key];

            $sql = array_merge($sql, $alteredSqls);
        }

        return ['sql' => $sql];
    }
}
