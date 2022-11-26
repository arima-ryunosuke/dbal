<?php

namespace Doctrine\DBAL\Plugin\Routine\Platforms;

use Doctrine\DBAL\Schema\Routine;

trait MySQLPlatform
{
    public function supportsRoutines(): bool
    {
        return true;
    }

    public function getCreateProcedureSQL(Routine $routine): string
    {
        return $this->_getCreateRoutineSQL($routine);
    }

    public function getCreateFunctionSQL(Routine $routine): string
    {
        return $this->_getCreateRoutineSQL($routine);
    }

    public function getDropProcedureSQL(string $routine, array $parameters = []): string
    {
        return $this->_getDropRoutineSQL(Routine::TYPE_PROCEDURE, $routine);
    }

    public function getDropFunctionSQL(string $routine, array $parameters = []): string
    {
        return $this->_getDropRoutineSQL(Routine::TYPE_FUNCTION, $routine);
    }

    public function getAlterProcedureSQL(Routine $routine): array
    {
        $cantAlterAttrs = ['statement', 'parameters', 'returnTypeDeclaration', 'deterministic', 'definer'];

        if (array_intersect($this->_getDiffKeys($routine, $routine->getOption('old')), $cantAlterAttrs)) {
            return parent::getAlterProcedureSQL($routine);
        }

        return [$this->_getAlterRoutineSQL($routine)];
    }

    public function getAlterFunctionSQL(Routine $routine): array
    {
        $cantAlterAttrs = ['statement', 'parameters', 'returnTypeDeclaration', 'deterministic', 'definer'];

        if (array_intersect($this->_getDiffKeys($routine, $routine->getOption('old')), $cantAlterAttrs)) {
            return parent::getAlterFunctionSQL($routine);
        }

        return [$this->_getAlterRoutineSQL($routine)];
    }

    private function _getCreateRoutineSQL(Routine $routine): string
    {
        $routineName = $routine->getLocalName();
        $type        = $routine->getType();
        $statement   = $routine->getStatement();
        $options     = $routine->getOptions();
        $definer     = $this->_quoteUserHostname($options['definer'] ?? '');

        $signatures = [];
        foreach ($options['parameters'] as $name => $param) {
            $signatures[] = trim(($type === Routine::TYPE_FUNCTION ? '' : $param['mode']) . " $name " . $param['typeDeclaration']);
        }
        $signatures = implode(", ", $signatures);

        return implode("\n", array_filter([
            "CREATE " . ($definer ? "DEFINER=$definer " : "") . "$type $routineName($signatures)",
            ($type === Routine::TYPE_FUNCTION) ? "RETURNS {$options['returnTypeDeclaration']}" : '',
            ($options['deterministic'] ? '' : 'NOT ') . "DETERMINISTIC",
            "{$options['dataAccess']}",
            $options['securityType'] ? "SQL SECURITY {$options['securityType']}" : "",
            "COMMENT {$this->quoteStringLiteral($options['comment'])}",
            $statement,
        ], 'strlen'));
    }

    private function _getAlterRoutineSQL(Routine $routine): string
    {
        $routineName = $routine->getLocalName();
        $type        = $routine->getType();
        $options     = $routine->getOptions();

        return implode("\n", array_filter([
            "ALTER $type $routineName",
            "{$options['dataAccess']}",
            $options['securityType'] ? "SQL SECURITY {$options['securityType']}" : "",
            "COMMENT {$this->quoteStringLiteral($options['comment'])}",
        ], 'strlen'));
    }

    private function _getDropRoutineSQL(string $type, string $routine): string
    {
        return "DROP $type $routine";
    }
}
