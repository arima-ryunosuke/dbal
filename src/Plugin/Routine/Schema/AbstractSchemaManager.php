<?php

namespace Doctrine\DBAL\Plugin\Routine\Schema;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Routine;
use Doctrine\DBAL\Schema\Schema;

trait AbstractSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\AbstractSchemaManager::createSchema()
     */
    protected function interceptIntrospectSchemaByRoutine(Schema $schema)
    {
        if ($this->_conn->getDatabasePlatform()->supportsRoutines()) {
            foreach ($this->listRoutines() as $routine) {
                $schema->addRoutine($routine);
            }
        }
    }

    public function listRoutines()
    {
        $database = $this->getDatabase(__METHOD__);

        return $this->_getPortableRoutineList(
            $this->selectRoutines($database)->fetchAllAssociative()
        );
    }

    protected function _getPortableRoutineList($routines)
    {
        $list = array_filter(array_map(fn($value) => $this->_getPortableRoutineDefinition($value), $routines));
        $keys = array_map(fn($value) => strtolower($value->getQuotedName($this->_platform)), $list);

        return array_combine($keys, $list);
    }

    protected function _getPortableRoutineDefinition($routine)
    {
        assert(isset($routine['name'], $routine['statement']));
        $name = $routine['name'];
        $stmt = $routine['statement'];
        unset($routine['name'], $routine['statement']);

        $routine['returnType']            = strlen($routine['returnType'] ?? '') ? $routine['returnType'] : null;
        $routine['returnTypeDeclaration'] = strlen($routine['returnTypeDeclaration'] ?? '') ? $routine['returnTypeDeclaration'] : null;

        return new Routine($name, $stmt, $routine);
    }

    protected function selectRoutines(string $databaseName): Result
    {
        throw Exception::notSupported(__METHOD__); // @codeCoverageIgnore
    }

    public function createRoutine(Routine $routine)
    {
        /** @see AbstractPlatform::getCreateProcedureSQL(), AbstractPlatform::getCreateFunctionSQL() */
        $type = $routine->getType();
        $this->_conn->executeStatement(
            $this->_platform->{"getCreate{$type}SQL"}($routine, $routine->getOption('parameters')),
        );
    }

    public function dropRoutine(string $name, string $type)
    {
        /** @see AbstractPlatform::getDropProcedureSQL(), AbstractPlatform::getDropFunctionSQL() */
        $this->_conn->executeStatement(
            $this->_platform->{"getDrop{$type}SQL"}($name),
        );
    }
}
