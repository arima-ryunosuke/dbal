<?php

namespace Doctrine\DBAL\Plugin\Trigger\Schema;

use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Trigger;
use PhpToken;

/**
 * @codeCoverageIgnore this is implemented to broaden one's horizons
 */
trait SQLiteSchemaManager
{
    protected function _getPortableTriggerDefinition($trigger): Trigger
    {
        $tokens = PhpToken::tokenize('<?php ' . $trigger['sql']);
        $begin  = null;
        $end    = null;
        foreach ($tokens as $n => $token) {
            if (strcasecmp($token->text, 'BEGIN') === 0) {
                $begin = $n;
            }
            if (strcasecmp($token->text, 'END') === 0) {
                $end = $n;
            }
        }

        assert($begin !== null && $end !== null);
        $stmtTokens           = array_slice($tokens, $begin, $end - $begin + 1);
        $stmtTexts            = array_column($stmtTokens, 'text');
        $trigger['statement'] = trim(implode('', $stmtTexts));

        unset($trigger['sql']);

        return parent::_getPortableTriggerDefinition($trigger);
    }

    protected function selectTriggers(string $databaseName): Result
    {
        $sql = <<<SQL
            SELECT
                name     AS "name",
                tbl_name AS "tableName",
                sql      AS "sql"
            FROM sqlite_master
            WHERE type = 'trigger'
        SQL;

        return $this->connection->executeQuery($sql);
    }
}
