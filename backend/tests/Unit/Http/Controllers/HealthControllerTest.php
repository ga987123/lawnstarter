<?php

declare(strict_types=1);

use App\Http\Controllers\HealthController;

it('returns ok status JSON', function (): void {
    $controller = new HealthController();
    $response = $controller();

    expect($response->getStatusCode())->toBe(200);
    $data = $response->getData(true);
    expect($data)->toBe(['status' => 'ok']);
});
