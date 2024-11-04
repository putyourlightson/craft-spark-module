<?php

/**
 * Tests the Spark functions.
 */

use craft\elements\Entry;
use putyourlightson\spark\twigextensions\SparkFunctions;
use Twig\Error\SyntaxError;

test('Test the `spark()` function by method', function(string $method) {
    $value = SparkFunctions::spark('template', method: $method);
    expect($value)
        ->toStartWith('$$' . $method . '(')
        ->toContain('template');

    if ($method === 'get') {
        expect($value)
            ->not->toContain('csrf');
    } else {
        expect($value)
            ->toContain('csrf');
    }
})->with([
    'get',
    'post',
    'put',
    'patch',
    'delete',
]);

test('Test that the `spark()` function throws an exception when an object variable exists', function() {
    SparkFunctions::spark('template', ['entry' => new Entry()]);
})->throws(SyntaxError::class);

test('Test the `sparkStore()` function', function() {
    $value = SparkFunctions::sparkStore(['a' => 1, 'b' => 'x']);
    expect($value)
        ->toBe('{"a":1,"b":"x"}');
});

test('Test that the `sparkStore()` function throws an exception when an object item exists', function() {
    SparkFunctions::sparkStore(['a' => 1, 'b' => new Entry()]);
})->throws(SyntaxError::class);
