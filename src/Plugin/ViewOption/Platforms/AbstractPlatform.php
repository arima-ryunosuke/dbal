<?php

namespace Doctrine\DBAL\Plugin\ViewOption\Platforms;

/**
 * @codeCoverageIgnore
 */
trait AbstractPlatform
{
    public function getReplaceViewSQL(string $name, string $sql, array $options = []): array
    {
        return [
            $this->getDropViewSQL($name),
            $this->getCreateViewSQL($name, $sql, $options),
        ];
    }
}
