<?php

namespace App\Http\Middleware\Settings;

use App\Services\Settings\SettingsMailConfigurator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyMailSettings
{
    public function __construct(private SettingsMailConfigurator $mailSettings) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->mailSettings->apply();

        return $next($request);
    }
}
