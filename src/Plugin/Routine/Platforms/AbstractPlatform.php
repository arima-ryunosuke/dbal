<?php

namespace Doctrine\DBAL\Plugin\Routine\Platforms;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Routine;

/**
 * @codeCoverageIgnore
 */
trait AbstractPlatform
{
    public function supportsRoutines(): bool
    {
        return false;
    }

    public function getCreateProcedureSQL(Routine $routine): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    public function getCreateFunctionSQL(Routine $routine): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    public function getDropProcedureSQL(string $routine, array $parameters = []): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    public function getDropFunctionSQL(string $routine, array $parameters = []): string
    {
        throw Exception::notSupported(__METHOD__);
    }

    public function getAlterProcedureSQL(Routine $routine): array
    {
        return [
            $this->getDropProcedureSQL($routine->getQuotedName($this), $routine->getOption('old')->getOption('parameters')),
            $this->getCreateProcedureSQL($routine),
        ];
    }

    public function getAlterFunctionSQL(Routine $routine): array
    {
        return [
            $this->getDropFunctionSQL($routine->getQuotedName($this), $routine->getOption('old')->getOption('parameters')),
            $this->getCreateFunctionSQL($routine),
        ];
    }

    protected function _getDiffKeys(Routine $new, Routine $old)
    {
        $newattrs = ['statement' => $new->getStatement()] + $new->getOptions();
        $oldattrs = ['statement' => $old->getStatement()] + $old->getOptions();

        $diffkeys = [];
        foreach ($oldattrs as $key => $value) {
            if (! array_key_exists($key, $newattrs) || $newattrs[$key] !== $value) {
                $diffkeys[] = $key;
            }
        }
        return $diffkeys;
    }
}
