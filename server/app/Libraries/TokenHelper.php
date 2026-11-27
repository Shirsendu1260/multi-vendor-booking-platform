<?php

namespace App\Libraries;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TokenHelper {
    public static function createTokens(User $user, string $ipAddress, string $userAgent): array {
        // Create access token
        // Set the TTL (time to live) for the access token based on the configuration
        $accessToken = Auth::setTTL(config('jwt.ttl'))->login($user);

        // Create refresh JWT token which contains user's ID
        $refreshToken = Auth::setTTL(config('jwt.refresh_ttl'))->login($user);
        $refreshTokenStr = (string) $refreshToken;

        // Store refresh token in database
        RefreshToken::create([
            'fk_user_id' => $user->id,
            'token_hash' => Hash::make($refreshTokenStr),
            'ip_address' => $ipAddress ?? null,
            'user_agent' => $userAgent ?? null,
            'expires_at' => now()->addMinutes(config('jwt.refresh_ttl')),
        ]);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshTokenStr,

            // 'bearar' is a common type for access tokens in OAuth 2.0, indicating that the token is a bearer token, 
            // which means that whoever possesses the token can use it to access the associated resources.
            'token_type' => 'bearer',

            // Auth::factory()->getTTL() means "get the time to live (TTL) for the token in minutes." 
            // We multiply by 60 to convert it to seconds, which is a common format for APIs.
            // It is for the access token, not the refresh token. 
            // The refresh token's expiration is managed separately in the database.
            'expires_in' => Auth::factory()->getTTL() * 60, // in seconds
        ];
    }

    public static function validateRefreshToken(int $userId, string $plainRefreshToken): bool {
        // Get the refresh token record from the database for this user and the provided plain refresh token
        $refreshTokenRecord = RefreshToken::findRefreshTokenRecordByUserAndToken($userId, $plainRefreshToken);

        // If no record is found, return false, else true
        return $refreshTokenRecord !== null;
    }
    
    public static function refreshTokenCookie(string $refreshToken) {
        $isLocal = app()->environment('local');

        return cookie(
            'refresh_token', // Cookie name
            $refreshToken, // Value (raw refresh token)
            config('jwt.refresh_ttl'), // Lifetime in minutes
            '/', // Path '/' means the cookie is valid for all routes
            null, // null = current host domain only (not shared with subdomains)

            // secure: true = cookie is only sent over HTTPS (required for SameSite=None)
            $isLocal ? false : true,

            // httpOnly: true = JavaScript cannot access the cookie, helping prevent XSS-based token theft
            true,

            false, // raw: false = Laravel URL-encodes the cookie value automatically

            // sameSite: none = allows the cookie to be sent in cross-site requests (required in production)
            // sameSite: lax = suitable for local development when frontend and backend use the same site
            // 'lax' = sends cookies with same-site requests and cross-site top-level navigations (e.g., 
            // clicking a link), but not cross-site fetch/AJAX requests
            $isLocal ? 'lax' : 'none'
        );
    }
}