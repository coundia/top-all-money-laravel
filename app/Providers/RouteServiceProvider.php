<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

function configureRateLimiting(): void{
    RateLimiter::for('auth',
        function ($request){
            return Limit::perMinute(10)
                ->by($request->ip());
        });
}
