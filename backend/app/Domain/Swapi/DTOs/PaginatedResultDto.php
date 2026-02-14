<?php

declare(strict_types=1);

namespace App\Domain\Swapi\DTOs;

/**
 * Wraps a paginated list of items with pagination metadata.
 *
 * @template T
 */
final readonly class PaginatedResultDto
{
    /**
     * @param list<T> $items
     */
    public function __construct(
        public array $items,
        public int $currentPage,
        public int $totalPages,
        public int $totalRecords,
        public bool $hasNextPage,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'items' => array_map(
                fn(object $item): array => method_exists($item, 'toArray') ? $item->toArray() : (array) $item,
                $this->items,
            ),
            'meta' => [
                'current_page' => $this->currentPage,
                'total_pages' => $this->totalPages,
                'total_records' => $this->totalRecords,
                'has_next_page' => $this->hasNextPage,
            ],
        ];
    }
}
