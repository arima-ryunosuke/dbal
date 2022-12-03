<?php

namespace Doctrine\DBAL\Plugin\IndexOption\Schema;

trait Index
{
    /**
     * @used-by \Doctrine\DBAL\Schema\Index::getQuotedColumns()
     */
    protected function interceptGetQuotedColumnsByIndexOption(array $columns): array
    {
        if ($this->hasOption('expression')) {
            $columns[] = '(' . $this->getOption('expression') . ')';
        }

        return ['columns' => $columns];
    }

    public function addOption(string $name, mixed $value): self
    {
        $this->options[strtolower($name)] = $value;

        return $this;
    }
}
