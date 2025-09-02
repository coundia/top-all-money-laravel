<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Create the application for testing.
     */
    public function createApplication(): Application
    {
        // Charge l'app depuis bootstrap/app.php (Laravel 11/12)
        $app = require __DIR__ . '/../bootstrap/app.php';

        // Démarre le kernel de test (cache config, providers, etc.)
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
