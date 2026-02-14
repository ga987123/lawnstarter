<?php

declare(strict_types=1);

namespace App\Domain\Swapi\DTOs;

final readonly class FilmDto
{
    /**
     * @param list<string> $characters
     */
    public function __construct(
        public int $id,
        public string $title,
        public int $episodeId,
        public string $director,
        public string $producer,
        public string $releaseDate,
        public string $openingCrawl,
        public array $characters,
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
            episodeId: (int) ($properties['episode_id'] ?? 0),
            director: (string) ($properties['director'] ?? ''),
            producer: (string) ($properties['producer'] ?? ''),
            releaseDate: (string) ($properties['release_date'] ?? ''),
            openingCrawl: (string) ($properties['opening_crawl'] ?? ''),
            characters: is_array($properties['characters'] ?? null) ? array_values($properties['characters']) : [],
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
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'episode_id' => $this->episodeId,
            'director' => $this->director,
            'producer' => $this->producer,
            'release_date' => $this->releaseDate,
            'opening_crawl' => $this->openingCrawl,
            'characters' => $this->characters,
        ];
    }

    /**
     * Slim representation for search result lists.
     *
     * @return array{id: int, name: string}
     */
    public function toSearchArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->title,
        ];
    }
}
