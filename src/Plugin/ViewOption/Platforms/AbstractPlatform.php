<?php

namespace Doctrine\DBAL\Plugin\ViewOption\Platforms;

use Doctrine\DBAL\Schema\View;

/**
 * @codeCoverageIgnore
 */
trait AbstractPlatform
{
    public function getAlterViewSQL(View $view): array
    {
        return [
            $this->getDropViewSQL($view->getName()),
            $this->getCreateViewSQL($view->getName(), $view->getSql(), $view->getOptions()),
        ];
    }
}
