<?php
namespace Doctrine\DBAL\Types\MySql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class SpatialType extends Type
{

    public static function addSpatialTypes()
    {
        $addedTypes = array();
        
        $files = scandir(__DIR__);
        foreach ($files as $file) {
            if ($file[0] === '.') {
                continue;
            }
            if ($file === basename(__FILE__)) {
                continue;
            }
            
            $basename = basename($file, '.php');
            $name = preg_replace('/Type$/', '', $basename);
            $typeName = strtolower($name);
            $className = __NAMESPACE__ . '\\' . $basename;
            
            if (! Type::hasType($typeName)) {
                Type::addType($typeName, $className);
                $addedTypes[$typeName] = Type::getType($typeName);
            }
        }
        
        return $addedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        $parts = explode('\\', get_class($this));
        $name = end($parts);
        return strtolower(preg_replace('/Type$/', '', $name));
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return strtoupper($this->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return (null === $value) ? null : $value;
    }
}
