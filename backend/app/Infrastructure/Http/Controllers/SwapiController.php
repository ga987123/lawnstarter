<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\Services\SwapiService;
use App\Infrastructure\Http\Requests\GetPersonRequest;
use App\Infrastructure\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SwapiController
{
    public function __construct(
        private readonly SwapiService $swapiService,
    ) {}

    public function show(GetPersonRequest $request, int $id): JsonResponse
    {
        $person = $this->swapiService->getPerson($id);

        return ApiResponse::data($person);
    }

    public function searchPeople(Request $request): JsonResponse
    {
        $queryParams = $request->query();
        $people = $this->swapiService->searchPeople($queryParams);

        return ApiResponse::data($people);
    }

    public function searchFilms(Request $request): JsonResponse
    {
        $queryParams = $request->query();
        $films = $this->swapiService->searchFilms($queryParams);

        return ApiResponse::data($films);
    }
}
