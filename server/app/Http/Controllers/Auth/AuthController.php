<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Libraries\ApiResponse;
use App\Libraries\TokenHelper;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private AuthService $service) {
        
    }

    public function register(RegisterRequest $request) {
        $data = $request->validated(); // gives only the validated fields (name, email, password, phone)
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->userAgent();

        $result = $this->service->register($data);

        // Get refresh token and unset the tokens array
        $refreshToken = $result['tokens']['refresh_token'];
        unset($result['tokens']['refresh_token']);

        return ApiResponse::success($result, __('auth.register_success'), 201)
            ->withCookie(TokenHelper::refreshTokenCookie($refreshToken));
    }

    public function login(LoginRequest $request) {
        $credentials = $request->validated();
        $result = $this->service->login($credentials);

        // Get refresh token and unset the tokens array
        $refreshToken = $result['tokens']['refresh_token'];
        unset($result['tokens']['refresh_token']);

        return ApiResponse::success($result, __('auth.login_success'), 200)
            ->withCookie(TokenHelper::refreshTokenCookie($refreshToken));
    }

    public function refresh(Request $request) {
        $data['refresh_token'] = $request->cookie('refresh_token');
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->userAgent();

        $result = $this->service->refresh($data);

        // Get refresh token and unset the tokens array
        $refreshToken = $result['tokens']['refresh_token'];
        unset($result['tokens']['refresh_token']);

        return ApiResponse::success($result, __('auth.tokens_refreshed'), 200)
            ->withCookie(TokenHelper::refreshTokenCookie($refreshToken));
    }

    public function logout(Request $request) {
        $data['refresh_token'] = $request->cookie('refresh_token');
        $data['access_token'] = $request->bearerToken();

        $this->service->logout($data);

        return ApiResponse::success(null, __('auth.logout_success'), 200)
            ->withCookie(cookie()->forget('refresh_token'));
    }

    public function activeSessions(Request $request) {
        $user = Auth::user();
        $result = $this->service->activeSessions($user);
        return ApiResponse::success($result, __('auth.active_sessions_fetched'), 200);
    }

    public function revokeSession(Request $request, string $uuid) {
        $user = Auth::user();
        $this->service->revokeSession($user, $uuid);
        return ApiResponse::success(null, __('auth.session_revoke_success'), 200);
    }
}
