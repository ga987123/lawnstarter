<?php

declare(strict_types=1);

namespace App\Infrastructure\Clients\Swapi;

/**
 * Wraps SWAPI API JSON response and provides normalized access to result and properties.
 *
 * SWAPI responses may have:
 * - Single resource: { "result": { "properties": { ... } } }
 * - List (people):  { "results": [ { "uid": 1, ... } ] }
 * - List (films):   { "result": [ { "uid": 1, "properties": { ... } } ] }
 */
final readonly class SwapiResponseWrapper
{
    /**
     * @param array<string, mixed> $body Raw JSON response body
     */
    public function __construct(
        private array $body,
    ) {}

    /**
     * Get the list of results (for list endpoints).
     * Returns "results" if present, otherwise "result" if it is an array.
     *
     * @return list<array<string, mixed>>
     */
    public function getResults(): array
    {
        if (isset($this->body['results']) && is_array($this->body['results'])) {
            return array_values($this->body['results']);
        }

        $result = $this->body['result'] ?? null;
        if (is_array($result) && isset($result[0])) {
            return array_values($result);
        }

        return [];
    }

    /**
     * Get the single result object (for detail endpoints).
     *
     * @return array<string, mixed>
     */
    public function getResult(): array
    {
        $result = $this->body['result'] ?? [];

        return is_array($result) ? $result : [];
    }

    /**
     * Get properties from the result (for single resource: result.properties).
     *
     * @return array<string, mixed>
     */
    public function getProperties(): array
    {
        $result = $this->getResult();

        $properties = $result['properties'] ?? $result;

        return is_array($properties) ? $properties : [];
    }

    /**
     * Get properties from a single result item (for list items that have uid + properties).
     *
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    public static function getItemProperties(array $item): array
    {
        $properties = $item['properties'] ?? $item;

        return is_array($properties) ? $properties : [];
    }

    /**
     * Get the total number of records reported by SWAPI.
     */
    public function getTotalRecords(): int
    {
        return (int) ($this->body['total_records'] ?? 0);
    }

    /**
     * Get the total number of pages reported by SWAPI.
     */
    public function getTotalPages(): int
    {
        return (int) ($this->body['total_pages'] ?? 1);
    }

    /**
     * Whether there is a next page of results.
     */
    public function hasNextPage(): bool
    {
        return !empty($this->body['next']);
    }

    /**
     * Create wrapper from raw response body.
     *
     * @param array<string, mixed> $body
     */
    public static function fromBody(array $body): self
    {
        return new self($body);
    }
}
