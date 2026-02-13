<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Wraps API payload in a consistent JSON structure and returns a JsonResponse.
 */
final class ApiResponse
{
    /**
     * Return a JSON response with data wrapped under the "data" key.
     *
     * @param array<string, mixed>|object $data Single item (with toArray()) or array of items
     * @param int $status HTTP status code
     * @return JsonResponse
     */
    public static function data(array|object $data, int $status = 200): JsonResponse
    {
        $payload = [
            'data' => self::normalize($data),
        ];

        return response()->json($payload, $status);
    }

    /**
     * Normalize data to array: call toArray() on objects that have it, recurse on arrays.
     *
     * @param array<string, mixed>|object $data
     * @return array<int|string, mixed>
     */
    private static function normalize(array|object $data): array
    {
        if (is_object($data) && method_exists($data, 'toArray')) {
            return $data->toArray();
        }

        if (!is_array($data)) {
            return (array) $data;
        }

        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = is_object($value) && method_exists($value, 'toArray')
                ? $value->toArray()
                : $value;
        }

        return $result;
    }
}
