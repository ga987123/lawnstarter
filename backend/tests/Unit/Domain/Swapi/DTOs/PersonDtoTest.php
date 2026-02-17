<?php

declare(strict_types=1);

use App\Domain\Swapi\DTOs\PersonDto;

it('fromSwapiResponse builds DTO from valid array', function (): void {
    $data = [
        'name' => 'Luke',
        'height' => '172',
        'mass' => '77',
        'birth_year' => '19BBY',
        'gender' => 'male',
        'skin_color' => 'fair',
        'hair_color' => 'blond',
        'eye_color' => 'blue',
        'films' => ['https://swapi.tech/api/films/1'],
    ];

    $dto = PersonDto::fromSwapiResponse(1, $data);

    expect($dto->id)->toBe(1)
        ->and($dto->name)->toBe('Luke')
        ->and($dto->birthYear)->toBe('19BBY')
        ->and($dto->films)->toBe(['https://swapi.tech/api/films/1']);
});

it('fromSwapiResponse uses empty string for missing keys', function (): void {
    $dto = PersonDto::fromSwapiResponse(2, []);

    expect($dto->name)->toBe('')
        ->and($dto->height)->toBe('')
        ->and($dto->films)->toBe([]);
});

it('fromResultItems builds list of PersonDto from SWAPI items', function (): void {
    $items = [
        ['uid' => 1, 'properties' => ['name' => 'Luke', 'height' => '172', 'mass' => '77', 'birth_year' => '19BBY', 'gender' => 'male', 'skin_color' => '', 'hair_color' => '', 'eye_color' => '', 'films' => []]],
        ['uid' => 2, 'properties' => ['name' => 'Leia', 'height' => '150', 'mass' => '49', 'birth_year' => '19BBY', 'gender' => 'female', 'skin_color' => '', 'hair_color' => '', 'eye_color' => '', 'films' => []]],
    ];

    $result = PersonDto::fromResultItems($items);

    expect($result)->toHaveCount(2)
        ->and($result[0]->id)->toBe(1)
        ->and($result[0]->name)->toBe('Luke')
        ->and($result[1]->id)->toBe(2)
        ->and($result[1]->name)->toBe('Leia');
});

it('toArray returns expected keys', function (): void {
    $dto = PersonDto::fromSwapiResponse(1, ['name' => 'Test', 'height' => '180', 'mass' => '80', 'birth_year' => '20BBY', 'gender' => 'male', 'skin_color' => '', 'hair_color' => '', 'eye_color' => '', 'films' => []]);

    $arr = $dto->toArray();

    expect($arr)->toHaveKeys(['id', 'name', 'height', 'mass', 'birth_year', 'gender', 'skin_color', 'hair_color', 'eye_color', 'films'])
        ->and($arr['birth_year'])->toBe('20BBY');
});

it('toSearchArray returns id and name only', function (): void {
    $dto = PersonDto::fromSwapiResponse(1, ['name' => 'Luke', 'height' => '172', 'mass' => '77', 'birth_year' => '19BBY', 'gender' => 'male', 'skin_color' => '', 'hair_color' => '', 'eye_color' => '', 'films' => []]);

    $arr = $dto->toSearchArray();

    expect($arr)->toBe(['id' => 1, 'name' => 'Luke']);
});
