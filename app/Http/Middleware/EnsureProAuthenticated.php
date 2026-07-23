<?php

namespace App\Http\Middleware;

use App\Models\ShopPro;
use App\Services\IframeTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $pro = Auth::guard('pro')->user();

        if (!$pro && $request->query('_itoken')) {
            $tokenService = app(IframeTokenService::class);
            $proId = $tokenService->validate($request->query('_itoken'));

            if ($proId) {
                $pro = ShopPro::where('planipets_pro_id', $proId)->where('is_active', true)->first();
                if ($pro) {
                    Auth::guard('pro')->login($pro);
                }
            }
        }

        if (!$pro) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('pro.login.page');
        }

        if (!$pro->is_active) {
            Auth::guard('pro')->logout();

            return redirect()->route('pro.login.page')
                ->with('error', 'Your account has been deactivated.');
        }

        return $next($request);
    }
}