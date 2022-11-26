<?php

namespace Doctrine\DBAL\Plugin\Routine\Schema;

use Doctrine\DBAL\Plugin\Routine\Schema\Exception\RoutineAlreadyExists;
use Doctrine\DBAL\Plugin\Routine\Schema\Exception\RoutineDoesNotExist;
use Doctrine\DBAL\Schema\Routine;

trait Schema
{
    /** @var Routine[] */
    protected array $_routines = [];

    /**
     * @used-by \Doctrine\DBAL\Schema\Schema::__clone()
     */
    protected function intercept__CloneByRoutine(): array
    {
        foreach ($this->_routines as $k => $routine) {
            $this->_routines[$k] = clone $routine;
        }

        return [];
    }

    /** @return Routine[] */
    public function getRoutines(): array
    {
        return $this->_routines;
    }

    public function getRoutine(string $routineName): Routine
    {
        $name = $this->getFullQualifiedAssetName($routineName);
        if (! isset($this->_routines[$name])) {
            throw RoutineDoesNotExist::new($name);
        }

        return $this->_routines[$name];
    }

    public function hasRoutine(string $routineName): bool
    {
        $name = $this->getFullQualifiedAssetName($routineName);

        return isset($this->_routines[$name]);
    }

    public function addRoutine(Routine $routine): self
    {
        $routineName = $this->normalizeName($routine);

        if (isset($this->_routines[$routineName])) {
            throw RoutineAlreadyExists::new($routineName);
        }

        $this->_routines[$routineName] = $routine;

        $this->_createNamespaceBy($routine);

        return $this;
    }

    public function dropRoutine(string $routineName): self
    {
        $name = $this->getFullQualifiedAssetName($routineName);
        $this->getRoutine($name);
        unset($this->_routines[$name]);

        return $this;
    }
}
