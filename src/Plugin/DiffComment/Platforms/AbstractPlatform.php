<?php

namespace Doctrine\DBAL\Plugin\DiffComment\Platforms;

use Doctrine\DBAL\Types\Type;

/**
 * @codeCoverageIgnore
 */
trait AbstractPlatform
{
    protected bool $___enableDiffComment = false;

    public function enableDiffComment(bool $enable): void
    {
        $this->___enableDiffComment = $enable;
    }

    public function buildDiffComment(array $diff): string
    {
        if (! $this->___enableDiffComment) {
            return '';
        }

        $stringify = function ($value) {
            if ($value instanceof Type) {
                return Type::getTypeRegistry()->lookupName($value);
            }
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        };

        $array_flatten = function ($array, $delimiter, $parents = []) use (&$array_flatten) {
            $result = [];
            foreach ($array as $k => $v) {
                $keys   = $parents;
                $keys[] = $k;
                if (is_iterable($v)) {
                    $result += $array_flatten($v, $delimiter, $keys);
                } else {
                    $result[implode($delimiter, $keys)] = $v;
                }
            }
            return $result;
        };

        $olds    = array_map($stringify, $array_flatten($diff[0], '.'));
        $news    = array_map($stringify, $array_flatten($diff[1], '.'));
        $changed = array_diff_assoc($olds, $news);

        if (! $changed) {
            return '';
        }

        //return "\n/*\n" . implode("\n", array_map(fn($name) => "  $name: {$olds[$name]} => {$news[$name]}", array_keys($changed))) . "\n*/\n";
        return "\n" . implode("\n", array_map(fn($name) => "-- $name: {$olds[$name]} => {$news[$name]}", array_keys($changed))) . "\n";
    }
}
