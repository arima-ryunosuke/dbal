<?php

namespace Doctrine\DBAL\Plugin\View\Platforms;

use Doctrine\DBAL\Schema\View;

trait MySQLPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getCreateViewSQL()
     */
    protected function interceptGetCreateViewSQLByView(string $query, array $options): array
    {
        $checkOption = $this->_getCheckOption($options);
        if ($checkOption !== null) {
            return ['query' => "$query $checkOption"];
        }

        return [];
    }

    public function getAlterViewSQL(View $view): array
    {
        $query = 'ALTER VIEW ' . $view->getLocalName() . ' AS ' . $view->getSql();

        $checkOption = $this->_getCheckOption($view->getOptions());
        if ($checkOption !== null) {
            $query .= ' ' . $checkOption;
        }

        return [$query];
    }

    private function _getCheckOption(array $options): ?string
    {
        $checkOption = strtoupper($options['checkOption'] ?? 'NONE');
        if ($checkOption !== 'NONE') {
            return "WITH $checkOption CHECK OPTION";
        }

        return null;
    }
}
