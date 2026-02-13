<?php

declare(strict_types=1);

use App\Domain\Swapi\Contracts\SwapiGatewayInterface;
use App\Domain\Swapi\DTOs\PersonDto;
use App\Domain\Swapi\Exceptions\SwapiNotFoundException;
use App\Domain\Swapi\Exceptions\SwapiUnavailableException;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void {
    Event::fake();
});

it('returns a normalized person from swapi', function (): void {
    $mockGateway = Mockery::mock(SwapiGatewayInterface::class);
    $mockGateway->shouldReceive('fetchPerson')
        ->with(1)
        ->once()
        ->andReturn(new PersonDto(
            id: 1,
            name: 'Luke Skywalker',
            height: '172',
            mass: '77',
            birthYear: '19BBY',
            gender: 'male',
        ));

    $this->app->instance(SwapiGatewayInterface::class, $mockGateway);

    $response = $this->getJson('/api/swapi/people/1');

    $response
        ->assertOk()
        ->assertJsonPath('data.name', 'Luke Skywalker')
        ->assertJsonPath('data.id', 1)
        ->assertJsonStructure([
            'data' => ['id', 'name', 'height', 'mass', 'birth_year', 'gender'],
        ]);
});

it('returns 404 when person is not found', function (): void {
    $mockGateway = Mockery::mock(SwapiGatewayInterface::class);
    $mockGateway->shouldReceive('fetchPerson')
        ->with(999)
        ->once()
        ->andThrow(new SwapiNotFoundException(999));

    $this->app->instance(SwapiGatewayInterface::class, $mockGateway);

    $response = $this->getJson('/api/swapi/people/999');

    $response
        ->assertStatus(404)
        ->assertJsonPath('status', 404)
        ->assertJsonPath('title', 'Not Found');
});

it('returns 502 when swapi is unavailable', function (): void {
    $mockGateway = Mockery::mock(SwapiGatewayInterface::class);
    $mockGateway->shouldReceive('fetchPerson')
        ->with(1)
        ->once()
        ->andThrow(new SwapiUnavailableException);

    $this->app->instance(SwapiGatewayInterface::class, $mockGateway);

    $response = $this->getJson('/api/swapi/people/1');

    $response
        ->assertStatus(502)
        ->assertJsonPath('status', 502)
        ->assertJsonPath('title', 'Bad Gateway');
});

it('rejects invalid person id', function (): void {
    $response = $this->getJson('/api/swapi/people/0');

    $response->assertStatus(422);
});
