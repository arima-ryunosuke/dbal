<?php

namespace Doctrine\DBAL\Plugin\CustomType\Platforms;

use Doctrine\DBAL\Types\Exception\TypesAlreadyExists;
use Doctrine\DBAL\Types\Type;

trait AbstractPlatform
{
    public function addCustomType(string $dbType, Type $type): self
    {
        $typename = $type->getName();
        $registry = Type::getTypeRegistry();

        if ($registry->has($typename)) {
            if (get_class($registry->get($typename)) !== get_class($type)) {
                throw TypesAlreadyExists::new($typename);
            }
        } else {
            $registry->register($typename, $type);
        }

        $this->registerDoctrineTypeMapping($dbType, $typename);

        return $this;
    }
}
