<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;
use App\Models\Permission;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $jabatanId = $user->jabatan_id;

        $routeName = $request->route()->getName();

        // Find the menu associated with the current route
        $menu = Menu::where('url', $routeName)->first();

        // If no menu is found for the route, allow access by default
        // You might want to change this behavior based on your security policy
        if (!$menu) {
            return $next($request);
        }

        // Check if the user's jabatan has 'read' permission for this menu
        $hasPermission = Permission::where('jabatan_id', $jabatanId)
                                   ->where('menu_id', $menu->id)
                                   ->where('can_read', true)
                                   ->exists();

        if (!$hasPermission) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
