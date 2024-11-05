<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\spark\models;

use putyourlightson\spark\Spark;

class StoreModel
{
    private array $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * This exists so that `store.{name}` and `store.{name}({value})` will work in Twig.
     */
    public function __call(string $name, array $arguments)
    {
        if (empty($arguments)) {
            return $this->get($name);
        }

        return $this->set($name, $arguments[0]);
    }

    /**
     * Returns the value in the store.
     */
    public function get(string $name): mixed
    {
        return $this->values[$name] ?? null;
    }

    /**
     * Returns the values in the store.
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Sets a value in the store.
     */
    public function set(string $name, mixed $value): static
    {
        $this->setNestedValue($name, $value);

        Spark::getInstance()->events->store($this->getNestedValue($name, $value));

        return $this;
    }

    /**
     * Sets multiple values in the store.
     */
    public function setValues(array $values): static
    {
        foreach ($values as $name => $value) {
            $this->values[$name] = $value;
        }

        Spark::getInstance()->events->store($values);

        return $this;
    }

    /**
     * Sets a nested value in the store using dot notation in the name.
     */
    private function setNestedValue(string $name, mixed $value): void
    {
        $parts = explode('.', $name);
        $current = &$this->values;
        foreach ($parts as $part) {
            if (!isset($current[$part])) {
                $current[$part] = [];
            }
            $current = &$current[$part];
        }
        $current = $value;
    }

    /**
     * Returns a nested value using dot notation in the name.
     */
    private function getNestedValue(string $name, mixed $value): array
    {
        $parts = explode('.', $name);
        $nestedValue = [];
        $current = &$nestedValue;
        foreach ($parts as $part) {
            $current[$part] = [];
            $current = &$current[$part];
        }
        $current = $value;

        return $nestedValue;
    }
}
