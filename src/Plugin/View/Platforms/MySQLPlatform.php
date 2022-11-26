<?php

namespace Doctrine\DBAL\Plugin\View\Platforms;

use Doctrine\DBAL\Schema\View;

trait MySQLPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getCreateViewSQL()
     */
    protected function interruptGetCreateViewSQLByView($name, $sql, $options)
    {
        return "CREATE {$this->_getViewSQL($name, $sql, $options)}";
    }

    public function getAlterViewSQL(View $view): array
    {
        return ["ALTER {$this->_getViewSQL($view->getName(), $view->getSql(), $view->getOptions())}"];
    }

    private function _getViewSQL($name, $sql, $options): string
    {
        $definer      = $this->_quoteUserHostname($options['definer'] ?? '');
        $securityType = strtoupper($options['securityType'] ?? '');
        $checkOption  = strtoupper($options['checkOption'] ?? 'NONE');

        return implode(" ", array_filter([
            ($definer ? "DEFINER=$definer" : ""),
            $securityType ? "SQL SECURITY {$securityType}" : "",
            "VIEW $name AS $sql",
            $checkOption !== 'NONE' ? "WITH {$checkOption} CHECK OPTION" : "",
        ], 'strlen'));
    }
}
