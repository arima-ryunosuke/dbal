<?php

namespace Doctrine\DBAL\Plugin\Utility\Platforms;

trait MySQLPlatform
{
    private function _quoteUserHostname(string $userHostname)
    {
        if (! strlen($userHostname)) {
            return $userHostname;
        }

        [$user, $hostname] = explode('@', $userHostname, 2) + [1 => null];

        if ($hostname === null) {
            return $this->quoteSingleIdentifier($user);
        }

        return implode('@', [
            $this->quoteSingleIdentifier($user),
            $this->quoteSingleIdentifier($hostname),
        ]);
    }
}
