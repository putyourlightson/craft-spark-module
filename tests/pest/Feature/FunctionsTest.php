<?php

/**
 * Tests the Spark functions.
 */

use craft\elements\Entry;
use putyourlightson\spark\twigextensions\SparkFunctions;
use Twig\Error\SyntaxError;

test('Test creating an action', function(string $method) {
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

test('Test that creating an action containing an object variable throws an exception', function() {
    SparkFunctions::spark('template', ['entry' => new Entry()]);
})->throws(SyntaxError::class);

test('Test creating a store', function() {
    $value = SparkFunctions::sparkStore(['a' => 1, 'b' => 'x']);
    expect($value)
        ->toBe('{"a":1,"b":"x"}');
});

test('Test creating a nested store', function() {
    $value = SparkFunctions::sparkStore(['a' => 1, 'b' => ['c' => 2, 'd' => 3]]);
    expect($value)
        ->toBe('{"a":1,"b":{"c":2,"d":3}}');
});

test('Test that creating a store containing an object throws an exception', function() {
    SparkFunctions::sparkStore(['a' => 1, 'b' => new Entry()]);
})->throws(SyntaxError::class);

test('Test that creating a nested store containing an object throws an exception', function() {
    SparkFunctions::sparkStore(['a' => 1, 'b' => ['c' => 2, 'd' => new Entry()]]);
})->throws(SyntaxError::class);
