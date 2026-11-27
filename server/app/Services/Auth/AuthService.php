<?php

namespace App\Services\Auth;

use App\Libraries\TokenHelper;
use App\Models\RefreshToken;
use App\Models\User;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService {
    public function register(array $data): array {
        // If the user already exists, throw an exception
        $userExists = User::existsByEmail($data['email']);
        if($userExists) {
            // withMessages() is a static factory method that returns an already-constructed ValidationException instance
            // We don't need to use 'new'.
            throw ValidationException::withMessages([
                'email' => [__('auth.user_email_already_exists')]
            ]);
        }

        // Create the user, role is set to 'customer' by default
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
        ]);

        // Create tokens
        $tokens = TokenHelper::createTokens(
            $user, 
            $data['ip_address'] ?? null,
            $data['user_agent'] ?? null
        );

        $result = [
            'user' => $user,
            'tokens' => $tokens,
        ];

        return $result;
    }

    public function login(array $credentials): array {
        // Attempt to authenticate the user
        $attemptAuth = Auth::setTTL(config('jwt.ttl'))->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ]);
        if(!$attemptAuth) {
            throw new AuthenticationException(__('auth.invalid_credentials'));
        }

        // Get the authenticated user and issue new pair of tokens (access + refresh)
        $user = Auth::user();
        $tokens = TokenHelper::createTokens(
            $user, 
            $credentials['ip_address'] ?? null,
            $credentials['user_agent'] ?? null
        );

        $result = [
            'user' => $user,
            'tokens' => $tokens,
        ];

        return $result;
    }

    public function refresh(array $data): array {
        // If the refresh token is not provided, throw an exception
        $plainRefreshToken = $data['refresh_token'] ?? null;
        if(!$plainRefreshToken) {
            throw new AuthenticationException(__('auth.refresh_token_required'));
        }

        // Validation 1:
        // If it is provided, validate it (the JWT) and get the user associated with it
        // Temporarily extend the allowed token lifespan to match the refresh window (so it isn't rejected as expired),
        // manually pass in the raw refresh token, and verify its signature and validity to fetch and return the matching 
        // User from the database.
        // In simple words, this means "check if the refresh token is valid and not expired, and get the user associated with it."
        $user = Auth::setTTL(config('jwt.refresh_ttl'))->setToken($plainRefreshToken)->authenticate();
        if(!$user) {
            throw new AuthenticationException(__('auth.invalid_refresh_token'));
        }

        // Validation 2:
        // Validate the refresh token against the refresh_tokens table too, to ensure it is still valid and has not been 
        // revoked or expired.
        $isRefreshTokenValid = TokenHelper::validateRefreshToken($user->id, $plainRefreshToken);
        if(!$isRefreshTokenValid) {
            throw new AuthenticationException(__('auth.invalid_refresh_token'));
        }

        // If the refresh token is absolutely valid, issue new pair of tokens (access + refresh)
        $tokens = TokenHelper::createTokens(
            $user,
            $data['ip_address'] ?? null,
            $data['user_agent'] ?? null
        );

        $result = [
            'user' => $user,
            'tokens' => $tokens,
        ];

        return $result;
    }

    public function logout(array $data): void {
        // If the tokens are not provided, throw an exception
        $plainRefreshToken = $data['refresh_token'] ?? null;
        $plainAccessToken = $data['access_token'] ?? null;
        if(!$plainRefreshToken || !$plainAccessToken) {
            throw new AuthenticationException(__('auth.tokens_required'));
        }

        // Validation 1:
        // Validate the refresh token (the JWT) and get the user associated with it
        $user = Auth::setTTL(config('jwt.refresh_ttl'))->setToken($plainRefreshToken)->authenticate();
        if(!$user) {
            throw new AuthenticationException(__('auth.invalid_refresh_token'));
        }

        // Validation 2:
        // Validate the refresh token against the refresh_tokens table too, to ensure it is still valid
        $isRefreshTokenValid = TokenHelper::validateRefreshToken($user->id, $plainRefreshToken);
        if(!$isRefreshTokenValid) {
            throw new AuthenticationException(__('auth.invalid_refresh_token'));
        }

        // If the refresh token is valid, revoke it from the database 
        $isRevoked = RefreshToken::revokeRefreshToken($user->id, $plainRefreshToken);
        if(!$isRevoked) {
            throw new ModelNotFoundException(__('auth.refresh_token_revoke_failed'));
        }

        // Now invalidate the refresh token (the JWT) itself and revoke it, if it is valid
        $refreshToken = Auth::getToken();
        if($refreshToken) {
            Auth::invalidate(true);
        }
        else {
            throw new Exception(__('auth.refresh_token_revoke_failed'));
        }

        // Validation 3:
        // Validate the access token (the JWT) and get the user associated with it
        $user = Auth::setTTL(config('jwt.ttl'))->setToken($plainAccessToken)->authenticate();
        if(!$user) {
            throw new AuthenticationException(__('auth.invalid_access_token'));
        }

        // Now invalidate the access token (the JWT) itself and revoke it, if it is valid
        $accessToken = Auth::getToken();
        if($accessToken) {
            Auth::invalidate(true);
        }
        else {
            throw new Exception(__('auth.access_token_revoke_failed'));
        }
    }

    public function activeSessions(User $user): array {
        return RefreshToken::getActiveSessions($user->id);
    }

    public function revokeSession(User $user, int|string $refreshTokenId): void {
        $isRefreshTokenRevoked = RefreshToken::revokeActiveSession($user->id, $refreshTokenId);

        if(!$isRefreshTokenRevoked) {
            throw new ModelNotFoundException(__('auth.session_revoke_failed'));
        }
    }
}