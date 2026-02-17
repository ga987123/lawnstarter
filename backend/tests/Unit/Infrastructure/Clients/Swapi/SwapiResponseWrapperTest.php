<?php

declare(strict_types=1);

use App\Infrastructure\Clients\Swapi\SwapiResponseWrapper;

it('fromBody and getProperties return properties for single resource', function (): void {
    $body = [
        'result' => [
            'properties' => [
                'name' => 'Luke Skywalker',
                'height' => '172',
            ],
        ],
    ];

    $wrapper = SwapiResponseWrapper::fromBody($body);
    $properties = $wrapper->getProperties();

    expect($properties)->toBe(['name' => 'Luke Skywalker', 'height' => '172']);
});

it('getProperties returns result when no properties key', function (): void {
    $body = [
        'result' => [
            'name' => 'Luke',
        ],
    ];

    $wrapper = SwapiResponseWrapper::fromBody($body);
    $properties = $wrapper->getProperties();

    expect($properties)->toBe(['name' => 'Luke']);
});

it('getResults returns results array for list response', function (): void {
    $body = [
        'results' => [
            ['uid' => 1, 'name' => 'Luke'],
            ['uid' => 2, 'name' => 'Leia'],
        ],
    ];

    $wrapper = SwapiResponseWrapper::fromBody($body);
    $results = $wrapper->getResults();

    expect($results)->toHaveCount(2)
        ->and($results[0]['uid'])->toBe(1);
});

it('getItemProperties returns properties from item', function (): void {
    $item = ['uid' => 1, 'properties' => ['name' => 'Luke']];

    $props = SwapiResponseWrapper::getItemProperties($item);

    expect($props)->toBe(['name' => 'Luke']);
});
