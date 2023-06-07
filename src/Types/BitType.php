<?php

namespace Doctrine\DBAL\Types;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Type that maps ab SQL BIT to a PHP int.
 */
class BitType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'bit';
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $length = '';

        if (isset($column['length']) && $column['length']) {
            $length = '(' . $column['length'] . ')';
        }

        return 'BIT' . $length;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value) || ctype_digit("$value")) {
            return (int) $value;
        }

        if (is_int($value)) {
            return $value;
        }

        return array_sum(array_map('ord', str_split($value, 1)));
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        return (int) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindingType(): ParameterType
    {
        return ParameterType::INTEGER;
    }
}
