<?php

declare(strict_types=1);

namespace App\Domain\Swapi\DTOs;

final readonly class RelatedResourceDto
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}

    /**
     * @return array{id: int, name: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
