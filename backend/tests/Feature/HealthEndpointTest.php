<?php

declare(strict_types=1);

it('returns ok status from health endpoint', function (): void {
    /** @var \Tests\TestCase $this */
    $response = $this->getJson('/api/health');

    $response
        ->assertOk()
        ->assertExactJson([
            'status' => 'ok',
        ]);
});
