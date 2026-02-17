<?php

declare(strict_types=1);

use App\Application\Services\StarwarsServiceInterface;
use App\Http\Controllers\StarWarsController;
use Illuminate\Http\Request;
use Tests\Mocks\SwapiMocks;

it('getPerson returns data response from service', function (): void {
    $person = ['id' => 1, 'name' => 'Luke Skywalker', 'films' => []];
    $service = Mockery::mock(StarwarsServiceInterface::class);
    $service->shouldReceive('getPerson')->with(1)->once()->andReturn($person);

    $controller = new StarWarsController($service);
    $response = $controller->getPerson(1);

    expect($response->getStatusCode())->toBe(200);
    $data = $response->getData(true);
    expect($data)->toHaveKey('data')->and($data['data'])->toBe($person);
});

it('getFilm returns data response from service', function (): void {
    $film = ['id' => 1, 'title' => 'A New Hope', 'characters' => []];
    $service = Mockery::mock(StarwarsServiceInterface::class);
    $service->shouldReceive('getFilm')->with(1)->once()->andReturn($film);

    $controller = new StarWarsController($service);
    $response = $controller->getFilm(1);

    expect($response->getStatusCode())->toBe(200);
    $data = $response->getData(true);
    expect($data)->toHaveKey('data')->and($data['data'])->toBe($film);
});

it('searchPeople passes query params and returns paginated response', function (): void {
    $paginated = SwapiMocks::paginatedPeople(2);
    $service = Mockery::mock(StarwarsServiceInterface::class);
    $service->shouldReceive('searchPeople')->with(['name' => 'luke'])->once()->andReturn($paginated);

    $request = Request::create('/api/swapi/people', 'GET', ['name' => 'luke']);
    $controller = new StarWarsController($service);
    $response = $controller->searchPeople($request);

    expect($response->getStatusCode())->toBe(200);
    $json = $response->getData(true);
    expect($json)->toHaveKeys(['data', 'meta'])
        ->and($json['data'])->toHaveCount(2)
        ->and($json['meta'])->toHaveKey('total_records');
});

it('searchFilms passes query params and returns data as search arrays', function (): void {
    $films = SwapiMocks::filmDtoList(2);
    $service = Mockery::mock(StarwarsServiceInterface::class);
    $service->shouldReceive('searchFilms')->with(['name' => 'hope'])->once()->andReturn($films);

    $request = Request::create('/api/swapi/films', 'GET', ['name' => 'hope']);
    $controller = new StarWarsController($service);
    $response = $controller->searchFilms($request);

    expect($response->getStatusCode())->toBe(200);
    $json = $response->getData(true);
    expect($json)->toHaveKey('data')->and($json['data'])->toHaveCount(2);
    expect($json['data'][0])->toHaveKeys(['id', 'name'])->and($json['data'][0]['name'])->toBe('A New Hope');
});
