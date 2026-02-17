<?php

declare(strict_types=1);

use App\Domain\Swapi\DTOs\FilmDto;

it('fromSwapiItem builds DTO from valid array', function (): void {
    $properties = [
        'title' => 'A New Hope',
        'episode_id' => 4,
        'director' => 'George Lucas',
        'producer' => 'Gary Kurtz',
        'release_date' => '1977-05-25',
        'opening_crawl' => 'It is a period of civil war.',
        'characters' => ['https://swapi.tech/api/people/1'],
    ];

    $dto = FilmDto::fromSwapiItem(1, $properties);

    expect($dto->id)->toBe(1)
        ->and($dto->title)->toBe('A New Hope')
        ->and($dto->episodeId)->toBe(4)
        ->and($dto->characters)->toBe(['https://swapi.tech/api/people/1']);
});

it('fromSwapiItem uses defaults for missing keys', function (): void {
    $dto = FilmDto::fromSwapiItem(2, []);

    expect($dto->title)->toBe('')
        ->and($dto->episodeId)->toBe(0)
        ->and($dto->characters)->toBe([]);
});

it('fromResultItems builds list of FilmDto', function (): void {
    $items = [
        ['uid' => 1, 'properties' => ['title' => 'A New Hope', 'episode_id' => 4, 'director' => '', 'producer' => '', 'release_date' => '', 'opening_crawl' => '', 'characters' => []]],
        ['uid' => 2, 'properties' => ['title' => 'Empire', 'episode_id' => 5, 'director' => '', 'producer' => '', 'release_date' => '', 'opening_crawl' => '', 'characters' => []]],
    ];

    $result = FilmDto::fromResultItems($items);

    expect($result)->toHaveCount(2)
        ->and($result[0]->id)->toBe(1)
        ->and($result[0]->title)->toBe('A New Hope')
        ->and($result[1]->title)->toBe('Empire');
});

it('toArray returns expected keys', function (): void {
    $dto = FilmDto::fromSwapiItem(1, ['title' => 'Test', 'episode_id' => 1, 'director' => 'D', 'producer' => 'P', 'release_date' => '2020-01-01', 'opening_crawl' => 'Crawl', 'characters' => []]);

    $arr = $dto->toArray();

    expect($arr)->toHaveKeys(['id', 'title', 'episode_id', 'director', 'producer', 'release_date', 'opening_crawl', 'characters'])
        ->and($arr['episode_id'])->toBe(1);
});

it('toSearchArray returns id and name from title', function (): void {
    $dto = FilmDto::fromSwapiItem(1, ['title' => 'A New Hope', 'episode_id' => 4, 'director' => '', 'producer' => '', 'release_date' => '', 'opening_crawl' => '', 'characters' => []]);

    $arr = $dto->toSearchArray();

    expect($arr)->toBe(['id' => 1, 'name' => 'A New Hope']);
});
