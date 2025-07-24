<?php

namespace Doctrine\DBAL\Plugin\Utility\Schema;

use Doctrine\DBAL\Schema\Name\OptionallyQualifiedName;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;

trait AbstractNamedObject
{
    public function getNamespace(): ?string
    {
        $name = $this->getObjectName();
        return match (true) {
            $name === null                           => null,
            $name instanceof UnqualifiedName         => null,
            $name instanceof OptionallyQualifiedName => $name->getQualifier()?->toString(),
        };
    }

    public function getLocalName(): ?string
    {
        $name = $this->getObjectName();
        return match (true) {
            $name === null                           => null,
            $name instanceof UnqualifiedName         => $name->toString(),
            $name instanceof OptionallyQualifiedName => $name->getUnqualifiedName()->toString(),
        };
    }
}
