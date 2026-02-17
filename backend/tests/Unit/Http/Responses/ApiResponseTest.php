<?php

declare(strict_types=1);

use App\Domain\Swapi\DTOs\PaginatedResultDto;
use App\Http\Responses\ApiResponse;
use Tests\Mocks\SwapiMocks;

it('data returns 200 with array wrapped in data key', function (): void {
    $payload = ['id' => 1, 'name' => 'Luke'];

    $response = ApiResponse::data($payload);

    expect($response->getStatusCode())->toBe(200);
    $json = $response->getData(true);
    expect($json)->toHaveKey('data')->and($json['data'])->toBe($payload);
});

it('data normalizes object with toArray to array', function (): void {
    $person = SwapiMocks::personDto(1);

    $response = ApiResponse::data($person);

    expect($response->getStatusCode())->toBe(200);
    $json = $response->getData(true);
    expect($json['data'])->toHaveKeys(['id', 'name', 'birth_year', 'films']);
});

it('paginated returns data and meta structure', function (): void {
    $paginated = SwapiMocks::paginatedPeople(1);

    $response = ApiResponse::paginated($paginated);

    expect($response->getStatusCode())->toBe(200);
    $json = $response->getData(true);
    expect($json)->toHaveKeys(['data', 'meta'])
        ->and($json['meta'])->toHaveKeys(['current_page', 'total_pages', 'total_records', 'has_next_page']);
});
