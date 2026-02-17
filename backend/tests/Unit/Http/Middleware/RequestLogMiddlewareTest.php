<?php

declare(strict_types=1);

use App\Http\Middleware\RequestLogMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

it('calls next and returns response', function (): void {
    Log::shouldReceive('channel')->with('request')->andReturnSelf();
    Log::shouldReceive('info')->once();

    $middleware = new RequestLogMiddleware();
    $request = Request::create('/api/health', 'GET');
    $expectedResponse = new Response('ok', 200);
    $next = fn () => $expectedResponse;

    $response = $middleware->handle($request, $next);

    expect($response)->toBe($expectedResponse)->and($response->getStatusCode())->toBe(200);
});
