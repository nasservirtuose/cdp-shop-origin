<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ShopPro;
use App\Services\IframeTokenService;
use App\Services\PlanipetsTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PlanipetsAuthController extends Controller
{
    public function login(Request $request, PlanipetsTokenService $tokenService)
    {
        $token = $request->query('token');
        if (empty($token)) {
            return redirect()->route('pro.login.page')->with('error', 'No authentication token provided.');
        }

        try {
            $payload = $tokenService->validate($token);
        } catch (\InvalidArgumentException $e) {
            Log::warning('[SHOP] Planipets JWT rejected', ['reason' => $e->getMessage(), 'ip' => $request->ip()]);

            return redirect()->route('pro.login.page')->with('error', 'Authentication failed: ' . $e->getMessage());
        }

        $pro = ShopPro::updateOrCreate(
            ['planipets_pro_id' => $payload['user_id']],
            [
                'email' => $payload['email'],
                'name' => $payload['name'] ?? null,
                'phone' => $payload['phone'] ?? null,
                'clinic_name' => $payload['clinic_name'] ?? null,
                'last_login_at' => now(),
                'last_synced_at' => now(),
                'is_active' => true,
            ]
        );

        if (!$pro->is_active) {
            return redirect()->route('pro.login.page')->with('error', 'Your account has been deactivated.');
        }

        Auth::guard('pro')->login($pro);
        Log::info('[SHOP] Pro logged in via Planipets SSO', ['pro_id' => $pro->planipets_pro_id]);

        $params = [];
        if ($request->query('embed') === '1') {
            $params['embed'] = '1';
            $params['_itoken'] = app(IframeTokenService::class)->generate($pro->planipets_pro_id);
        }

        return redirect()->route('pro.dashboard', $params);
    }

    public function logout(Request $request)
    {
        Auth::guard('pro')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $planipetsUrl = config('services.planipets.dashboard_url');

        return $planipetsUrl ? redirect()->away($planipetsUrl) : redirect()->route('pro.login.page');
    }

    public function showLoginPage()
    {
        if (Auth::guard('pro')->check()) {
            return redirect()->route('pro.dashboard');
        }

        return view('pro.login', ['planipetsUrl' => config('services.planipets.dashboard_url')]);
    }
}