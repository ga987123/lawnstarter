<?php

declare(strict_types=1);

use App\Application\Services\StarwarsService;
use App\Domain\Contracts\AppLoggerInterface;
use App\Domain\Swapi\Contracts\SwapiClientInterface;
use App\Domain\Swapi\DTOs\FilmDto;
use App\Domain\Swapi\DTOs\PaginatedResultDto;
use App\Domain\Swapi\DTOs\PersonDto;
use App\Domain\Swapi\DTOs\RelatedResourceDto;
use App\Domain\Swapi\Exceptions\SwapiNotFoundException;
use App\Domain\Swapi\Exceptions\SwapiUnavailableException;
use Illuminate\Support\Facades\Event;
use Tests\Mocks\SwapiMocks;

beforeEach(function (): void {
    Event::fake();
});

it('getPerson returns array with films resolved and dispatches QueryExecuted', function (): void {
    /** @var \Tests\TestCase $this */
    $person = SwapiMocks::personDto(1, ['films' => ['https://swapi.tech/api/films/1']]);
    $resolved = [new RelatedResourceDto(1, 'A New Hope')];

    $client = Mockery::mock(SwapiClientInterface::class);
    $client->shouldReceive('fetchPerson')->with(1)->once()->andReturn($person);
    $client->shouldReceive('resolveResourceNames')->with(['https://swapi.tech/api/films/1'])->once()->andReturn($resolved);

    $logger = Mockery::mock(AppLoggerInterface::class);
    $logger->shouldReceive('info')->once();

    $this->app->instance(SwapiClientInterface::class, $client);
    $this->app->instance(AppLoggerInterface::class, $logger);

    $service = $this->app->make(StarwarsService::class);
    $result = $service->getPerson(1);

    expect($result)->toBeArray()
        ->and($result['name'])->toBe('Luke Skywalker')
        ->and($result['films'])->toBe([['id' => 1, 'name' => 'A New Hope']]);

    Event::assertDispatched(\App\Application\Events\QueryExecuted::class);
});

it('getFilm returns array with characters resolved and dispatches FilmQueryExecuted', function (): void {
    /** @var \Tests\TestCase $this */
    $film = SwapiMocks::filmDto(1, ['characters' => ['https://swapi.tech/api/people/1']]);
    $resolved = [new RelatedResourceDto(1, 'Luke Skywalker')];

    $client = Mockery::mock(SwapiClientInterface::class);
    $client->shouldReceive('fetchFilm')->with(1)->once()->andReturn($film);
    $client->shouldReceive('resolveResourceNames')->with(['https://swapi.tech/api/people/1'])->once()->andReturn($resolved);

    $logger = Mockery::mock(AppLoggerInterface::class);
    $logger->shouldReceive('info')->once();

    $this->app->instance(SwapiClientInterface::class, $client);
    $this->app->instance(AppLoggerInterface::class, $logger);

    $service = $this->app->make(StarwarsService::class);
    $result = $service->getFilm(1);

    expect($result)->toBeArray()
        ->and($result['title'])->toBe('A New Hope')
        ->and($result['characters'])->toBe([['id' => 1, 'name' => 'Luke Skywalker']]);

    Event::assertDispatched(\App\Application\Events\FilmQueryExecuted::class);
});

it('searchPeople returns PaginatedResultDto and dispatches SearchQueryExecuted', function (): void {
    /** @var \Tests\TestCase $this */
    $paginated = SwapiMocks::paginatedPeople(2);

    $client = Mockery::mock(SwapiClientInterface::class);
    $client->shouldReceive('searchPeople')->with(['name' => 'luke'])->once()->andReturn($paginated);

    $logger = Mockery::mock(AppLoggerInterface::class);
    $logger->shouldReceive('info')->zeroOrMoreTimes();

    $this->app->instance(SwapiClientInterface::class, $client);
    $this->app->instance(AppLoggerInterface::class, $logger);

    $service = $this->app->make(StarwarsService::class);
    $result = $service->searchPeople(['name' => 'luke']);

    expect($result)->toBeInstanceOf(PaginatedResultDto::class)
        ->and($result->items)->toHaveCount(2);

    Event::assertDispatched(\App\Application\Events\SearchQueryExecuted::class, fn ($e) => $e->searchType === 'people' && $e->resultCount === 2);
});

it('searchFilms returns list and dispatches SearchQueryExecuted', function (): void {
    /** @var \Tests\TestCase $this */
    $films = SwapiMocks::filmDtoList(2);

    $client = Mockery::mock(SwapiClientInterface::class);
    $client->shouldReceive('searchFilms')->with(['name' => 'hope'])->once()->andReturn($films);

    $logger = Mockery::mock(AppLoggerInterface::class);
    $logger->shouldReceive('info')->zeroOrMoreTimes();

    $this->app->instance(SwapiClientInterface::class, $client);
    $this->app->instance(AppLoggerInterface::class, $logger);

    $service = $this->app->make(StarwarsService::class);
    $result = $service->searchFilms(['name' => 'hope']);

    expect($result)->toBeArray()->toHaveCount(2);

    Event::assertDispatched(\App\Application\Events\SearchQueryExecuted::class, fn ($e) => $e->searchType === 'films' && $e->resultCount === 2);
});

it('getPerson propagates SwapiNotFoundException', function (): void {
    /** @var \Tests\TestCase $this */
    $client = Mockery::mock(SwapiClientInterface::class);
    $client->shouldReceive('fetchPerson')->with(999)->once()->andThrow(new SwapiNotFoundException(999));

    $logger = Mockery::mock(AppLoggerInterface::class);
    $logger->shouldReceive('info')->once();

    $this->app->instance(SwapiClientInterface::class, $client);
    $this->app->instance(AppLoggerInterface::class, $logger);

    $service = $this->app->make(StarwarsService::class);

    $service->getPerson(999);
})->throws(SwapiNotFoundException::class);

it('getPerson propagates SwapiUnavailableException', function (): void {
    /** @var \Tests\TestCase $this */
    $client = Mockery::mock(SwapiClientInterface::class);
    $client->shouldReceive('fetchPerson')->with(1)->once()->andThrow(new SwapiUnavailableException);

    $logger = Mockery::mock(AppLoggerInterface::class);
    $logger->shouldReceive('info')->once();

    $this->app->instance(SwapiClientInterface::class, $client);
    $this->app->instance(AppLoggerInterface::class, $logger);

    $service = $this->app->make(StarwarsService::class);

    $service->getPerson(1);
})->throws(SwapiUnavailableException::class);
