<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Services\StarwarsService;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class StarWarsController
{
    public function __construct(
        private readonly StarwarsService $starwarsService,
    ) {}

    public function getPerson(int $id): JsonResponse
    {
        $person = $this->starwarsService->getPerson($id);

        return ApiResponse::data($person);
    }

    public function getFilm(int $id): JsonResponse
    {
        $film = $this->starwarsService->getFilm($id);

        return ApiResponse::data($film);
    }

    public function searchPeople(Request $request): JsonResponse
    {
        $queryParams = $request->query();
        $result = $this->starwarsService->searchPeople($queryParams);

        return ApiResponse::paginated($result);
    }

    public function searchFilms(Request $request): JsonResponse
    {
        $queryParams = $request->query();
        $films = $this->starwarsService->searchFilms($queryParams);

        return ApiResponse::data(
            array_map(fn($film) => $film->toSearchArray(), $films),
        );
    }
}
