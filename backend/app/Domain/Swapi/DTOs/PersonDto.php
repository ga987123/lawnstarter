<?php

declare(strict_types=1);

namespace App\Domain\Swapi\DTOs;

final readonly class PersonDto
{
    /**
     * @param list<string> $films
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $height,
        public string $mass,
        public string $birthYear,
        public string $gender,
        public string $skinColor,
        public string $hairColor,
        public string $eyeColor,
        public array $films,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromSwapiResponse(int $id, array $data): self
    {
        return new self(
            id: $id,
            name: (string) ($data['name'] ?? ''),
            height: (string) ($data['height'] ?? ''),
            mass: (string) ($data['mass'] ?? ''),
            birthYear: (string) ($data['birth_year'] ?? ''),
            gender: (string) ($data['gender'] ?? ''),
            skinColor: (string) ($data['skin_color'] ?? ''),
            hairColor: (string) ($data['hair_color'] ?? ''),
            eyeColor: (string) ($data['eye_color'] ?? ''),
            films: is_array($data['films'] ?? null) ? array_values($data['films']) : []
        );
    }

    /**
     * Build a list of PersonDto from SWAPI result items.
     *
     * @param  list<array<string, mixed>>  $items
     * @return list<self>
     */
    public static function fromResultItems(array $items): array
    {
        $people = [];
        foreach ($items as $item) {
            $uid = (int) ($item['uid']);
            $properties = $item['properties'] ?? $item;
            $properties = is_array($properties) ? $properties : [];
            $people[] = self::fromSwapiResponse($uid, $properties);
        }
        return $people;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'height' => $this->height,
            'mass' => $this->mass,
            'birth_year' => $this->birthYear,
            'gender' => $this->gender,
            'skin_color' => $this->skinColor,
            'hair_color' => $this->hairColor,
            'eye_color' => $this->eyeColor,
            'films' => $this->films,
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
            'name' => $this->name,
        ];
    }
}
