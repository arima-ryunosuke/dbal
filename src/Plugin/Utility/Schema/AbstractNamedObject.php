<?php

namespace Doctrine\DBAL\Plugin\Utility\Schema;

trait AbstractNamedObject
{
    public function getNamespace(): ?string
    {
        return $this->getObjectName()?->getQualifier()?->toString();
    }

    public function getLocalName(): string
    {
        return $this->getObjectName()->getUnqualifiedName()->toString();
    }
}
