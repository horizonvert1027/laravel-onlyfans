<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use App\Models\AdminSettings;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
      $settings = AdminSettings::first();
      
        if (! $request->expectsJson()) {
          session()->flash('login_required', true);
          return $settings->home_style == 0 ? route('login') : route('home');
        }
    }
}
