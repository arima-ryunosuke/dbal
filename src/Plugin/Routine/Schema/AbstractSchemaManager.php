<?php

namespace Doctrine\DBAL\Plugin\Routine\Schema;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\Exception\NotSupported;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Routine;
use Doctrine\DBAL\Schema\Schema;

trait AbstractSchemaManager
{
    /**
     * @used-by \Doctrine\DBAL\Schema\AbstractSchemaManager::introspectSchema()
     */
    protected function interceptIntrospectSchemaByRoutine(Schema $schema): array
    {
        if ($this->connection->getDatabasePlatform()->supportsRoutines()) {
            foreach ($this->listRoutines() as $routine) {
                $schema->addRoutine($routine);
            }
        }

        return [];
    }

    /** @return Routine[] */
    public function listRoutines(): array
    {
        $database = $this->getDatabase(__METHOD__);

        return $this->_getPortableRoutineList(
            $this->selectRoutines($database)->fetchAllAssociative()
        );
    }

    /** @return Routine[] */
    protected function _getPortableRoutineList(array $routines): array
    {
        $list = array_filter(array_map(fn($value) => $this->_getPortableRoutineDefinition($value), $routines));
        $keys = array_map(fn($value) => strtolower($value->getQuotedName($this->platform)), $list);

        return array_combine($keys, $list);
    }

    protected function _getPortableRoutineDefinition(array $routine): Routine
    {
        assert(isset($routine['name'], $routine['statement']));
        $name = $routine['name'];
        $stmt = $routine['statement'];
        unset($routine['name'], $routine['statement']);

        $routine['returnType']            = strlen($routine['returnType'] ?? '') ? $routine['returnType'] : null;
        $routine['returnTypeDeclaration'] = strlen($routine['returnTypeDeclaration'] ?? '') ? $routine['returnTypeDeclaration'] : null;

        return new Routine($name, $stmt, $routine);
    }

    protected function _getPortableRoutineParameter(array $parameters): array
    {
        $result = [];
        usort($parameters, fn($a, $b) => $a['position'] <=> $b['position']);
        foreach ($parameters as $parameter) {
            $name = $parameter['name'] ?? '';
            if (strlen($name)) {
                $result[$name] = array_diff_key($parameter, [
                    'name'     => null,
                    'position' => null,
                ]);
            }
        }
        return $result;
    }

    protected function selectRoutines(string $databaseName): Result
    {
        throw NotSupported::new(__METHOD__); // @codeCoverageIgnore
    }

    public function createRoutine(Routine $routine): void
    {
        /** @see AbstractPlatform::getCreateProcedureSQL(), AbstractPlatform::getCreateFunctionSQL() */
        $type = $routine->getType();
        $this->connection->executeStatement(
            $this->platform->{"getCreate{$type}SQL"}($routine, $routine->getOption('parameters')),
        );
    }

    public function dropRoutine(string $name, string $type): void
    {
        /** @see AbstractPlatform::getDropProcedureSQL(), AbstractPlatform::getDropFunctionSQL() */
        $this->connection->executeStatement(
            $this->platform->{"getDrop{$type}SQL"}($name),
        );
    }
}
