<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| Schedule the statistics recomputation to run every 5 minutes.
|
*/

Schedule::command('statistics:compute')->everyFiveMinutes();
