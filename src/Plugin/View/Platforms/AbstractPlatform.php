<?php

namespace Doctrine\DBAL\Plugin\View\Platforms;

use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\View;

/**
 * @codeCoverageIgnore
 */
trait AbstractPlatform
{
    /**
     * @used-by \Doctrine\DBAL\Platforms\MySQLPlatform::getAlterSchemaSQL()
     */
    protected function interceptGetAlterSchemaSQLByView(array $sql, SchemaDiff $diff): array
    {
        foreach ($diff->createdViews as $view) {
            $sql[] = $this->getCreateViewSQL($view->getLocalName(), $view->getSql(), $view->getOptions());
        }

        foreach ($diff->droppedViews as $view) {
            $sql[] = $this->getDropViewSQL($view->getLocalName());
        }

        foreach ($diff->alteredViews as $n => $view) {
            $alteredSqls       = $this->getAlterViewSQL($view);
            $key               = array_key_first($alteredSqls);
            $alteredSqls[$key] = $this->buildDiffComment($diff->viewDiffs[$n]) . $alteredSqls[$key];

            $sql = array_merge($sql, $alteredSqls);
        }

        return ['sql' => $sql];
    }

    public function getAlterViewSQL(View $view): array
    {
        return [
            $this->getDropViewSQL($view->getLocalName()),
            $this->getCreateViewSQL($view->getLocalName(), $view->getSql(), $view->getOptions()),
        ];
    }
}
