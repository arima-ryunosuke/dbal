<?php

namespace Doctrine\DBAL\Plugin\ViewOption\Schema;

use Doctrine\DBAL\Schema\View;

trait MySQLSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\MySQLSchemaManager::_getPortableViewDefinition()
     */
    protected function intercept_GetPortableViewDefinitionByViewOption(View $viewObject, array $view)
    {
        $viewObject->addOption('checkOption', $view['CHECK_OPTION']);
        $viewObject->addOption('updatable', filter_var($view['IS_UPDATABLE'], FILTER_VALIDATE_BOOLEAN));
        $viewObject->addOption('definer', $view['DEFINER']);
        $viewObject->addOption('securityType', $view['SECURITY_TYPE']);
    }
}
