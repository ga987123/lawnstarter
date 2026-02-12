<?php

declare(strict_types=1);

it('returns Swagger UI HTML at docs endpoint', function (): void {
    $response = $this->get('/api/docs');

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'text/html')
        ->assertSee('swagger-ui', false);
});

it('returns OpenAPI spec YAML at docs openapi endpoint', function (): void {
    $response = $this->get('/api/docs/openapi.yaml');

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/x-yaml')
        ->assertSee('openapi: 3.1.0', false);
});
