<?php

namespace Doctrine\DBAL\Plugin\AutoType\Platforms;

use Doctrine\DBAL\Types\AnyType;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\TypeRegistry;

trait AbstractPlatform
{
    private bool $enableAutoType = false;

    public function enableAutoType(bool $enable = true): self
    {
        $this->enableAutoType = $enable;

        // for testing
        if ($enable === false) {
            $names = (function () {
                $results = [];
                foreach ($this->instances as $name => $type) {
                    if ($type instanceof AnyType) {
                        $results[] = $name;
                        unset($this->instances[$name]);
                        unset($this->instancesReverseIndex[spl_object_id($type)]);
                    }
                }
                return $results;
            })->bindTo(Type::getTypeRegistry(), TypeRegistry::class)();

            foreach ($names as $name) {
                unset($this->doctrineTypeMapping[$name]);
            }
        }

        return $this;
    }

    /**
     * @used-by \Doctrine\DBAL\Platforms\AbstractPlatform::getDoctrineTypeMapping()
     */
    protected function interceptGetDoctrineTypeMappingByAutoType(string $dbType): array
    {
        if ($this->enableAutoType) {
            if (! $this->hasDoctrineTypeMappingFor($dbType)) {
                if (! Type::getTypeRegistry()->has($dbType)) {
                    Type::getTypeRegistry()->register($dbType, new AnyType());
                }

                $this->registerDoctrineTypeMapping($dbType, $dbType);
            }
        }

        return [];
    }
}
