<?php

namespace Doctrine\DBAL\Plugin\ViewOption\Platforms;

use Doctrine\DBAL\Schema\View;

trait MySQLPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getCreateViewSQL()
     */
    protected function interceptGetCreateViewSQLByViewOption($query, $options)
    {
        $checkOption = $this->_getCheckOption($options);
        if ($checkOption !== null) {
            return ['query' => "$query $checkOption"];
        }
    }

    public function getAlterViewSQL(View $view): array
    {
        $query = 'ALTER VIEW ' . $view->getName() . ' AS ' . $view->getSql();

        $checkOption = $this->_getCheckOption($view->getOptions());
        if ($checkOption !== null) {
            $query .= ' ' . $checkOption;
        }

        return [$query];
    }

    private function _getCheckOption($options): ?string
    {
        $checkOption = strtoupper($options['checkOption'] ?? 'NONE');
        if ($checkOption !== 'NONE') {
            return "WITH $checkOption CHECK OPTION";
        }

        return null;
    }
}
