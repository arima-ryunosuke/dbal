<?php

namespace Doctrine\DBAL\Plugin\Routine\Platforms;

use Doctrine\DBAL\Schema\Routine;

/**
 * @codeCoverageIgnore this is implemented to broaden one's horizons
 */
trait PostgreSQLPlatform
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
        return $this->_getDropRoutineSQL(Routine::TYPE_PROCEDURE, $routine, $parameters);
    }

    public function getDropFunctionSQL(string $routine, array $parameters = []): string
    {
        return $this->_getDropRoutineSQL(Routine::TYPE_FUNCTION, $routine, $parameters);
    }

    public function getAlterProcedureSQL(Routine $routine): array
    {
        $cantAlterAttrs = ['statement', 'parameters', 'returnTypeDeclaration', 'language'];

        if (array_intersect($this->_getDiffKeys($routine, $routine->getOption('old')), $cantAlterAttrs)) {
            return parent::getAlterProcedureSQL($routine);
        }

        return [$this->_getAlterRoutineSQL($routine)];
    }

    public function getAlterFunctionSQL(Routine $routine): array
    {
        $cantAlterAttrs = ['statement', 'parameters', 'returnTypeDeclaration', 'language'];

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

        $signatures = [];
        foreach ($options['parameters'] as $name => $param) {
            $signatures[] = $param['mode'] . " $name " . $param['typeDeclaration'];
        }
        $signatures = implode(", ", $signatures);

        return implode("\n", array_filter([
            "CREATE $type $routineName($signatures)",
            ($type === Routine::TYPE_FUNCTION) ? "RETURNS {$options['returnTypeDeclaration']}" : '',
            "AS \$\${$statement}\$\$",
            "LANGUAGE {$options['language']}",
            ($type === Routine::TYPE_FUNCTION) ? ($options['deterministic'] ? 'IMMUTABLE' : 'VOLATILE') : '',
            ($type === Routine::TYPE_FUNCTION) ? ($options['nullcall'] ? 'RETURNS NULL ON NULL INPUT' : 'CALLED ON NULL INPUT') : '',
        ], 'strlen'));
    }

    private function _getAlterRoutineSQL(Routine $routine): string
    {
        $routineName = $routine->getLocalName();
        $type        = $routine->getType();
        $options     = $routine->getOptions();

        return implode("\n", array_filter([
            "ALTER $type $routineName",
            ($type === Routine::TYPE_FUNCTION) ? ($options['deterministic'] ? 'IMMUTABLE' : 'VOLATILE') : '',
            ($type === Routine::TYPE_FUNCTION) ? ($options['nullcall'] ? 'RETURNS NULL ON NULL INPUT' : 'CALLED ON NULL INPUT') : '',
        ], 'strlen'));
    }

    private function _getDropRoutineSQL(string $type, string $routine, array $parameters): string
    {
        $signatures = [];
        foreach ($parameters as $name => $param) {
            $signatures[] = $param['mode'] . " $name " . $param['typeDeclaration'];
        }

        return "DROP $type $routine" . ($signatures ? '(' . implode(", ", $signatures) . ')' : '');
    }
}
