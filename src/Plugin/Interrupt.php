<?php

namespace Doctrine\DBAL\Plugin;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Interrupt extends Hook
{
}
