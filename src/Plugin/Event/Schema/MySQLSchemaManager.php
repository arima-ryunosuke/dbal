<?php

namespace Doctrine\DBAL\Plugin\Event\Schema;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;

trait MySQLSchemaManager
{
    protected function _getPortableEventDefinition($event)
    {
        $event['status'] = (static function ($status) {
            switch (strtoupper($status)) {
                default:
                    return $status; // @codeCoverageIgnore

                case 'ENABLED':
                    return 'ENABLE';
                case 'DISABLED':
                    return 'DISABLE';
                case 'SLAVESIDE_DISABLED':
                    return 'DISABLE ON SLAVE';
            }
        })($event['status'] ?? '');

        $event['interval'] = (static function ($field, $value) {
            $format  = 'P%04dY%02dM%02dDT%02dH%02dM%02dS';
            $default = ['Y' => 0, 'M' => 0, 'D' => 0, 'h' => 0, 'm' => 0, 's' => 0];
            switch (strtoupper($field)) {
                default:
                    throw new Exception("unknown intervalField '$field'"); // @codeCoverageIgnore

                case 'YEAR':
                    return vsprintf($format, array_replace($default, ['Y' => $value]));
                case 'QUARTER':
                    return vsprintf($format, array_replace($default, ['M' => $value * 3]));
                case 'MONTH':
                    return vsprintf($format, array_replace($default, ['M' => $value]));
                case 'WEEK':
                    return vsprintf($format, array_replace($default, ['D' => $value * 7]));
                case 'DAY':
                    return vsprintf($format, array_replace($default, ['D' => $value]));
                case 'HOUR':
                    return vsprintf($format, array_replace($default, ['h' => $value]));
                case 'MINUTE':
                    return vsprintf($format, array_replace($default, ['m' => $value]));
                case 'SECOND':
                    return vsprintf($format, array_replace($default, ['s' => $value]));

                case 'YEAR_MONTH':
                    [$Y, $M] = explode('-', $value);
                    return vsprintf($format, array_replace($default, ['Y' => $Y, 'M' => $M]));
                case 'DAY_HOUR':
                    [$D, $h] = preg_split('#[ :]#', $value);
                    return vsprintf($format, array_replace($default, ['D' => $D, 'h' => $h]));
                case 'DAY_MINUTE':
                    [$D, $h, $m] = preg_split('#[ :]#', $value);
                    return vsprintf($format, array_replace($default, ['D' => $D, 'h' => $h, 'm' => $m]));
                case 'DAY_SECOND':
                    [$D, $h, $m, $s] = preg_split('#[ :]#', $value);
                    return vsprintf($format, array_replace($default, ['D' => $D, 'h' => $h, 'm' => $m, 's' => $s]));
                case 'HOUR_MINUTE':
                    [$h, $m] = explode(':', $value);
                    return vsprintf($format, array_replace($default, ['h' => $h, 'm' => $m]));
                case 'HOUR_SECOND':
                    [$h, $m, $s] = explode(':', $value);
                    return vsprintf($format, array_replace($default, ['h' => $h, 'm' => $m, 's' => $s]));
                case 'MINUTE_SECOND':
                    [$m, $s] = explode(':', $value);
                    return vsprintf($format, array_replace($default, ['m' => $m, 's' => $s]));
            }
        })($event['intervalField'] ?? '', trim($event['intervalValue'] ?? '', '"\''));

        return parent::_getPortableEventDefinition($event);
    }

    protected function selectEvents(string $databaseName): Result
    {
        $sql = <<<SQL
            SELECT
                EVENT_NAME         AS `name`,
                TIME_ZONE          AS `timeZone`,
                STATUS             AS `status`,
                STARTS             AS `since`,
                ENDS               AS `until`,
                INTERVAL_VALUE     AS `intervalValue`,
                INTERVAL_FIELD     AS `intervalField`,
                ON_COMPLETION      AS `completion`,
                EVENT_BODY         AS `language`,
                EVENT_DEFINITION   AS `statement`,
                DEFINER            AS `definer`,
                EVENT_COMMENT      AS `comment`
            FROM information_schema.EVENTS
            WHERE EVENT_SCHEMA = ?
            ORDER BY EVENT_NAME
        SQL;

        return $this->_conn->executeQuery($sql, [$databaseName]);
    }
}
