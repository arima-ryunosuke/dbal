<?php

namespace Doctrine\DBAL\Plugin\Trigger\Schema;

use Doctrine\DBAL\Schema\Trigger;

trait SchemaDiff
{
    /** @var Trigger[] */
    public array $createdTriggers = [];

    /** @var Trigger[] */
    public array $droppedTriggers = [];

    /** @var Trigger[] */
    public array $alteredTriggers = [];

    public array $triggerDiffs = [];

    /**
     * @used-by \Doctrine\DBAL\Schema\SchemaDiff::isEmpty()
     */
    protected function interruptIsEmptyByTrigger(): ?bool
    {
        if (count($this->createdTriggers) || count($this->droppedTriggers) || count($this->alteredTriggers)) {
            return false;
        }

        return null;
    }
}
