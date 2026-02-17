<?php

declare(strict_types=1);

use App\Application\Jobs\RecomputeStatisticsJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;

it('dispatches RecomputeStatisticsJob when command is run', function (): void {
    Queue::fake();

    $exitCode = Artisan::call('statistics:compute');

    expect($exitCode)->toBe(0);
    Queue::assertPushed(RecomputeStatisticsJob::class);
});

it('outputs success message', function (): void {
    Queue::fake();

    Artisan::call('statistics:compute');

    expect(Artisan::output())->toContain('Statistics recomputation job dispatched.');
});
