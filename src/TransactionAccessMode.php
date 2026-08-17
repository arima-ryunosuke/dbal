<?php

declare(strict_types=1);

namespace Doctrine\DBAL;

enum TransactionAccessMode
{
    case READ_WRITE;
    case READ_ONLY;

    public function toSQL(): string
    {
        return match ($this) {
            \Doctrine\DBAL\TransactionAccessMode::READ_WRITE => 'READ WRITE',
            \Doctrine\DBAL\TransactionAccessMode::READ_ONLY  => 'READ ONLY',
        };
    }
}
