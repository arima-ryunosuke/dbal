<?php

namespace Doctrine\DBAL\Plugin\CustomType\Platforms;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Type;

trait AbstractPlatform
{
    public function addCustomType(string $dbType, Type $type)
    {
        $typename = $type->getName();
        $registry = Type::getTypeRegistry();

        if ($registry->has($typename)) {
            if (get_class($registry->get($typename)) !== get_class($type)) {
                throw Exception::typeExists($typename);
            }
        } else {
            $registry->register($typename, $type);
        }

        $this->registerDoctrineTypeMapping($dbType, $typename);
    }
}
