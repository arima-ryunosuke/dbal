<?php

namespace Doctrine\DBAL\Plugin\ViewOption\Platforms;

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

    public function getReplaceViewSQL(string $name, string $sql, array $options = []): array
    {
        $query = 'CREATE OR REPLACE VIEW ' . $name . ' AS ' . $sql;

        $checkOption = $this->_getCheckOption($options);
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
