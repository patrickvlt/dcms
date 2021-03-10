<?php

namespace Pveltrop\DCMS\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class HasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (config('dcms.hasPermissionMiddleware') == false){
            return $next($request);
        }

        $currentRouteName = request()->route()->getAction()['as'] ?? null;
        $userIsSuperAdmin = auth()->user() ? auth()->user()->hasRole(config('dcms.superAdminRole')) : false;

        $userPermissions = [];
        if (auth()->user()){
            $userPermissions = ($userIsSuperAdmin) ? Permission::all()->pluck('route') : auth()->user()->getAllPermissions()->pluck('route');
        }

        if (is_countable($userPermissions) && count($userPermissions) > 0){
            return $next($request);
        }

        abort(403);
    }
}
