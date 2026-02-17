<?php

declare(strict_types=1);

use App\Domain\Swapi\Contracts\SwapiClientInterface;
use App\Domain\Swapi\Exceptions\SwapiNotFoundException;
use App\Domain\Swapi\Exceptions\SwapiUnavailableException;
use Illuminate\Support\Facades\Event;
use Tests\Mocks\SwapiMocks;

beforeEach(function (): void {
    Event::fake();
});

it('returns a film by id', function (): void {
    /** @var \Tests\TestCase $this */
    $film = SwapiMocks::filmDto(1);
    $mockGateway = Mockery::mock(SwapiClientInterface::class);
    $mockGateway->shouldReceive('fetchFilm')->with(1)->once()->andReturn($film);
    $mockGateway->shouldReceive('resolveResourceNames')->with([])->once()->andReturn([]);

    $this->app->instance(SwapiClientInterface::class, $mockGateway);

    $response = $this->getJson('/api/swapi/films/1');

    $response
        ->assertOk()
        ->assertJsonPath('data.title', 'A New Hope')
        ->assertJsonPath('data.id', 1)
        ->assertJsonStructure([
            'data' => ['id', 'title', 'episode_id', 'director', 'opening_crawl', 'characters'],
        ]);
});

it('returns 404 when film is not found', function (): void {
    /** @var \Tests\TestCase $this */
    $mockGateway = Mockery::mock(SwapiClientInterface::class);
    $mockGateway->shouldReceive('fetchFilm')->with(999)->once()->andThrow(new SwapiNotFoundException(999));

    $this->app->instance(SwapiClientInterface::class, $mockGateway);

    $response = $this->getJson('/api/swapi/films/999');

    $response
        ->assertStatus(404)
        ->assertJsonPath('status', 404)
        ->assertJsonPath('title', 'Not Found');
});

it('returns 502 when swapi is unavailable for film', function (): void {
    /** @var \Tests\TestCase $this */
    $mockGateway = Mockery::mock(SwapiClientInterface::class);
    $mockGateway->shouldReceive('fetchFilm')->with(1)->once()->andThrow(new SwapiUnavailableException);

    $this->app->instance(SwapiClientInterface::class, $mockGateway);

    $response = $this->getJson('/api/swapi/films/1');

    $response
        ->assertStatus(502)
        ->assertJsonPath('status', 502)
        ->assertJsonPath('title', 'Bad Gateway');
});

it('rejects invalid film id', function (): void {
    /** @var \Tests\TestCase $this */
    $response = $this->getJson('/api/swapi/films/0');

    $response->assertStatus(404);
});

it('returns film list for search', function (): void {
    /** @var \Tests\TestCase $this */
    $films = SwapiMocks::filmDtoList(2);
    $mockGateway = Mockery::mock(SwapiClientInterface::class);
    $mockGateway->shouldReceive('searchFilms')->once()->andReturn($films);

    $this->app->instance(SwapiClientInterface::class, $mockGateway);

    $response = $this->getJson('/api/swapi/films?name=hope');

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'A New Hope');
});
