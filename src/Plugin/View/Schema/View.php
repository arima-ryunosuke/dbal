<?php

namespace Doctrine\DBAL\Plugin\View\Schema;

trait View
{
    protected array $_options = [];

    /**
     * @used-by \Doctrine\DBAL\Schema\View::__construct()
     */
    protected function intercept__constructByView(array $options): array
    {
        $this->_options = $options;

        return [];
    }

    public function addOption(string $name, mixed $value): self
    {
        $this->_options[$name] = $value;

        return $this;
    }

    public function hasOption(string $name): bool
    {
        return isset($this->_options[$name]);
    }

    public function getOption(string $name): mixed
    {
        return $this->_options[$name] ?? null;
    }

    public function getOptions(): array
    {
        return $this->_options;
    }
}
