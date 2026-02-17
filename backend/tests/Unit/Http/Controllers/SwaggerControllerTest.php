<?php

declare(strict_types=1);

use App\Http\Controllers\SwaggerController;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View as ViewFacade;

it('ui returns a view', function (): void {
    /** @var \Tests\TestCase $this */
    $mockView = Mockery::mock(View::class);
    ViewFacade::shouldReceive('make')->with('swagger', [], [])->once()->andReturn($mockView);

    $controller = new SwaggerController();
    $response = $controller->ui();

    expect($response)->toBe($mockView);
});

it('spec returns 200 with yaml content when file exists', function (): void {
    /** @var \Tests\TestCase $this */
    $yaml = "openapi: 3.1.0\ninfo:\n  title: API\n";
    $path = base_path('docs/openapi.yaml');

    File::shouldReceive('exists')->with($path)->once()->andReturn(true);
    File::shouldReceive('get')->with($path)->once()->andReturn($yaml);

    $controller = new SwaggerController();
    $response = $controller->spec();

    expect($response->getStatusCode())->toBe(200);
    expect($response->headers->get('Content-Type'))->toBe('application/x-yaml');
    expect($response->getContent())->toBe($yaml);
});

it('spec aborts 404 when file does not exist', function (): void {
    /** @var \Tests\TestCase $this */
    $path = base_path('docs/openapi.yaml');
    File::shouldReceive('exists')->with($path)->once()->andReturn(false);
    File::shouldNotReceive('get');

    $controller = new SwaggerController();

    $controller->spec();
})->throws(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
