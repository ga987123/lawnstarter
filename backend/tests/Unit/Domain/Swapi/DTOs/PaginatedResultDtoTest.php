<?php

declare(strict_types=1);

use App\Domain\Swapi\DTOs\PaginatedResultDto;
use App\Domain\Swapi\DTOs\PersonDto;
use Tests\Mocks\SwapiMocks;

it('toArray returns items and meta structure', function (): void {
    $items = [SwapiMocks::personDto(1)];
    $dto = new PaginatedResultDto(
        items: $items,
        currentPage: 1,
        totalPages: 5,
        totalRecords: 10,
        hasNextPage: true,
    );

    $arr = $dto->toArray();

    expect($arr)->toHaveKeys(['items', 'meta'])
        ->and($arr['meta'])->toBe([
            'current_page' => 1,
            'total_pages' => 5,
            'total_records' => 10,
            'has_next_page' => true,
        ])
        ->and($arr['items'])->toHaveCount(1)
        ->and($arr['items'][0])->toHaveKeys(['id', 'name']);
});
