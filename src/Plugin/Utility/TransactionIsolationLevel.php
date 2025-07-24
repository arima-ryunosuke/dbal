<?php

namespace Doctrine\DBAL\Plugin\Utility;

trait TransactionIsolationLevel
{
    public function toSQL(): string
    {
        return match ($this) {
            \Doctrine\DBAL\TransactionIsolationLevel::READ_UNCOMMITTED => 'READ UNCOMMITTED',
            \Doctrine\DBAL\TransactionIsolationLevel::READ_COMMITTED   => 'READ COMMITTED',
            \Doctrine\DBAL\TransactionIsolationLevel::REPEATABLE_READ  => 'REPEATABLE READ',
            \Doctrine\DBAL\TransactionIsolationLevel::SERIALIZABLE     => 'SERIALIZABLE',
        };
    }
}
