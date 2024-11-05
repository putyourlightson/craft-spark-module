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
     * Sets a value in the store, converting the name to a nested array using dot notation.
     */
    public function set(string $name, mixed $value): static
    {
        $parts = explode('.', $name);
        $currentValue = &$this->values;
        $store = [];
        $currentStore = &$store;

        foreach ($parts as $part) {
            if (!isset($currentValue[$part])) {
                $currentValue[$part] = [];
            }
            $currentValue = &$currentValue[$part];

            $currentStore[$part] = [];
            $currentStore = &$currentStore[$part];
        }

        $currentValue = $value;
        $currentStore = $value;

        Spark::getInstance()->events->store($store);

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
}
