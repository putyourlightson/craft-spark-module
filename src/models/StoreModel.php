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
        return $this->getNestedValue($name);
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

        Spark::getInstance()->events->store($this->getNestedArrayValue($name, $value));

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
     * Removes a value from the store.
     */
    public function remove(string $name): static
    {
        $this->removeNestedValue($name);

        Spark::getInstance()->events->store($this->getNestedArrayValue($name, null));

        return $this;
    }

    /**
     * Returns a nested value, or null if it does not exist.
     */
    private function getNestedValue(string $name): mixed
    {
        $parts = explode('.', $name);
        $current = &$this->values;
        foreach ($parts as $part) {
            if (!isset($current[$part])) {
                return null;
            }
            $current = &$current[$part];
        }

        return $current;
    }

    /**
     * Sets a nested value in the store while supporting dot notation in the name.
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
     * Removes a nested value from the store while supporting dot notation in the name.
     */
    private function removeNestedValue(string $name): void
    {
        $parts = explode('.', $name);
        $part = reset($parts);
        $current = &$this->values;
        $parent = &$current;
        foreach ($parts as $part) {
            if (!isset($current[$part])) {
                return;
            }
            $parent = &$current;
            $current = &$current[$part];
        }
        unset($parent[$part]);
    }

    /**
     * Returns a nested value while supporting dot notation in the name.
     */
    private function getNestedArrayValue(string $name, mixed $value): array
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
