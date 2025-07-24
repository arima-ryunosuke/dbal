<?php

namespace Doctrine\DBAL\Plugin\Utility\Schema;

use Doctrine\DBAL\Schema\AbstractAsset;

trait Schema
{
    private function _createNamespaceBy(AbstractAsset $asset)
    {
        $resolvedName  = $this->resolveName($asset);
        $namespaceName = $resolvedName->getNamespaceName();

        if ($namespaceName !== null) {
            if (
                ! $asset->isInDefaultNamespace($this->getName())
                && ! $this->hasNamespace($namespaceName)
            ) {
                $this->createNamespace($namespaceName);
            }
        } else {
            $this->usesUnqualifiedNames = true;
        }
    }
}
