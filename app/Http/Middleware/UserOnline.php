<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Auth;
use Cache;
use Carbon\Carbon;

class UserOnline
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      try {
        if (Auth::check()) {
            $expiresAt = Carbon::now()->addMinutes(1);
            Cache::put('is-online-' . Auth::user()->id, true, $expiresAt);

            if (! $request->is('messages/*') || $request->route()->getName() != 'live.data') {
              // last seen
              User::whereId(Auth::user()->id)->update([
                'last_seen' => (new \DateTime())->format('Y-m-d H:i:s')
              ]);
            }

        }
      } catch (\Exception $e) {
        //
      }

      return $next($request);
    }
}
