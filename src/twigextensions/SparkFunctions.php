<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\spark\twigextensions;

use Craft;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use putyourlightson\spark\models\ConfigModel;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Twig\Error\SyntaxError;

class SparkFunctions
{
    public const ALLOWED_METHODS = ['get', 'post', 'put', 'patch', 'delete'];

    /**
     * Returns the Spark URL endpoint, wrapped in an action function.
     */
    public static function spark(string $template, array $variables = [], string $method = 'get'): string
    {
        $method = self::getValidMethod($method);
        $url = self::sparkUrl($template, $variables, $method);

        return "$$$method('$url')";
    }

    /**
     * Returns the Spark URL endpoint.
     */
    public static function sparkUrl(string $template, array $variables = [], string $method = 'get'): string
    {
        $config = new ConfigModel([
            'siteId' => Craft::$app->getSites()->getCurrentSite()->id,
            'template' => $template,
            'variables' => $variables,
            'method' => self::getValidMethod($method),
        ]);

        if (!$config->validate()) {
            throw new SyntaxError(implode(' ', $config->getFirstErrors()));
        }

        return UrlHelper::actionUrl('spark-module', [
            'config' => $config->getHashed(),
        ]);
    }

    /**
     * Returns an array of values as a store.
     */
    public static function sparkStore(array $values): string
    {
        self::validateStoreValues($values);

        return Json::encode($values);
    }

    /**
     * Returns a class’s public properties as a store.
     */
    public static function sparkStoreFromClass(string $class, array $values = []): string
    {
        $classValues = self::getClassPropertyValues($class);

        foreach ($values as $key => $value) {
            $classValues[$key] = $value;
        }

        return self::sparkStore($classValues);
    }

    private static function validateStoreValues(array $values): void
    {
        foreach ($values as $value) {
            if (is_object($value)) {
                throw new SyntaxError('Store values cannot contain objects.');
            }

            if (is_array($value)) {
                self::validateStoreValues($value);
            }
        }
    }

    private static function getValidMethod(string $method): string
    {
        $method = strtolower($method);
        if (!in_array($method, static::ALLOWED_METHODS)) {
            throw new SyntaxError('Method must be one of ' . implode(', ', self::ALLOWED_METHODS));
        }

        return $method;
    }

    private static function getClassPropertyValues(string $class): array
    {
        if (!class_exists($class)) {
            throw new SyntaxError('Class `' . $class . '` could not be found. Ensure that the class exists and is autoloaded.');
        }

        $reflection = new ReflectionClass($class);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        $defaultValues = $reflection->getDefaultProperties();

        $values = [];
        foreach ($properties as $property) {
            $defaultValue = $defaultValues[$property->name] ?? '';
            $values[$property->name] = self::getPropertyValue($property, $defaultValue);
        }

        return $values;
    }

    private static function getPropertyValue(ReflectionProperty $property, mixed $defaultValue): mixed
    {
        $type = $property->getType();
        if (!($type instanceof ReflectionNamedType)) {
            return $defaultValue;
        }

        if ($type->isBuiltin()) {
            return $defaultValue;
        }

        $class = $type->getName();

        return self::getClassPropertyValues($class);
    }
}
