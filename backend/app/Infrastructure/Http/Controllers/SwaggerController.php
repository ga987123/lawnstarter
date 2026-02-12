<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

final class SwaggerController
{
    public function ui(): View
    {
        return view('swagger');
    }

    public function spec(): Response
    {
        $path = base_path('docs/openapi.yaml');

        if (! File::exists($path)) {
            abort(404);
        }

        return response(File::get($path), 200, [
            'Content-Type' => 'application/x-yaml',
        ]);
    }
}
