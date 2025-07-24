<?php

namespace Doctrine\DBAL\Plugin\Utility\Schema\ForeignKeyConstraint;

trait ReferentialAction
{
    public function hasAction(): bool
    {
        return match ($this) {
            \Doctrine\DBAL\Schema\ForeignKeyConstraint\ReferentialAction::CASCADE     => true,
            \Doctrine\DBAL\Schema\ForeignKeyConstraint\ReferentialAction::SET_DEFAULT => true,
            \Doctrine\DBAL\Schema\ForeignKeyConstraint\ReferentialAction::SET_NULL    => true,
            default                                                                   => false,
        };
    }
}
