<?php

namespace Doctrine\DBAL\Plugin\All\Schema;

/**
 * @used-by \Doctrine\DBAL\Schema\Schema
 */
trait Schema
{
    // @formatter:off,auto-generated:begin
    use \Doctrine\DBAL\Plugin\Event\Schema\Schema;
    use \Doctrine\DBAL\Plugin\Routine\Schema\Schema;
    use \Doctrine\DBAL\Plugin\Trigger\Schema\Schema;
    use \Doctrine\DBAL\Plugin\ViewOption\Schema\Schema;
    // @formatter:on,auto-generated:end

    private function _createNamespaceBy(\Doctrine\DBAL\Schema\AbstractAsset $asset)
    {
        $namespaceName = $asset->getNamespaceName();

        if (
            $namespaceName !== null
            && ! $asset->isInDefaultNamespace($this->getName())
            && ! $this->hasNamespace($namespaceName)
        ) {
            $this->createNamespace($namespaceName);
        }
    }
}
