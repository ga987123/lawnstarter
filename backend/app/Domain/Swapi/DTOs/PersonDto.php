<?php

declare(strict_types=1);

namespace App\Domain\Swapi\DTOs;

final readonly class PersonDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $height,
        public string $mass,
        public string $birthYear,
        public string $gender,
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
        ];
    }
}
