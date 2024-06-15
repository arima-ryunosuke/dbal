<?php

namespace Doctrine\DBAL\Plugin\AutoType\Platforms;

use Doctrine\DBAL\Types\AnyType;
use Doctrine\DBAL\Types\Type;

trait AbstractPlatform
{
    private bool $enableAutoType = false;

    public function enableAutoType(bool $enable = true): self
    {
        $this->enableAutoType = $enable;

        return $this;
    }

    /**
     * @used-by \Doctrine\DBAL\Platforms\AbstractPlatform::getDoctrineTypeMapping()
     */
    protected function interceptGetDoctrineTypeMappingByAutoType(string $dbType)
    {
        if ($this->enableAutoType) {
            if (! Type::getTypeRegistry()->has($dbType)) {
                $type = new AnyType();
                $type->setName($dbType);
                Type::getTypeRegistry()->register($dbType, $type);
            }

            if (! $this->hasDoctrineTypeMappingFor($dbType)) {
                $this->registerDoctrineTypeMapping($dbType, $dbType);
            }
        }
    }
}
