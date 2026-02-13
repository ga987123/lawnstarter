<?php

declare(strict_types=1);

namespace App\Domain\Swapi\DTOs;

final readonly class FilmDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $director,
        public string $releaseDate,
    ) {}

    /**
     * Build one FilmDto from SWAPI item id and properties array.
     *
     * @param  array<string, mixed>  $properties
     */
    public static function fromSwapiItem(int $id, array $properties): self
    {
        return new self(
            id: $id,
            title: (string) ($properties['title'] ?? ''),
            director: (string) ($properties['director'] ?? ''),
            releaseDate: (string) ($properties['release_date'] ?? ''),
        );
    }

    /**
     * Build a list of FilmDto from SWAPI result items.
     *
     * @param  list<array<string, mixed>>  $items
     * @return list<self>
     */
    public static function fromResultItems(array $items): array
    {
        $films = [];
        foreach ($items as $item) {
            $uid = (int) ($item['uid']);
            $properties = $item['properties'] ?? $item;
            $properties = is_array($properties) ? $properties : [];
            $films[] = self::fromSwapiItem($uid, $properties);
        }
        return $films;
    }

    /**
     * @param  array<string, mixed>  $item  SWAPI result item with uid and properties
     */
    public static function fromSwapiResult(array $item): self
    {
        $uid = (int) ($item['uid'] ?? 0);
        $properties = $item['properties'] ?? $item;

        return self::fromSwapiItem($uid, $properties);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'director' => $this->director,
            'release_date' => $this->releaseDate,
        ];
    }
}
