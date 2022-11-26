<?php

namespace Doctrine\DBAL\Plugin;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
abstract class Hook
{
    public function __construct(
        public int $priority = 0,
    ) {
    }
}
