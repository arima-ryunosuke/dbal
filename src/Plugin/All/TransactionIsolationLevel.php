<?php

namespace Doctrine\DBAL\Plugin\All;

/**
 * @used-by \Doctrine\DBAL\\TransactionIsolationLevel
 */
trait TransactionIsolationLevel
{
    use \Doctrine\DBAL\Plugin\Pluggable;
    use \Doctrine\DBAL\Plugin\Utility\TransactionIsolationLevel;
}
