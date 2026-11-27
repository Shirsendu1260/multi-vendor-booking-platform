<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

#[Fillable(['fk_user_id', 'token_hash', 'expires_at'])]
class RefreshToken extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }


    // Relationships //

    public function user() {
        return $this->belongsTo(User::class, 'fk_user_id');
    }


    // Model functions //

    public static function findRefreshTokenRecordByUserAndToken(int|string $userId, string $plainRefreshToken) {
        // Get the refresh token record/records from the database for this user
        $activeRefreshTokenRecords = self::where('fk_user_id', $userId)
            ->where('expires_at', '>', now()) // Check if the refresh token is not expired
            ->where('revoked_at', null) // Check if the refresh token is not revoked
            ->get();

        // If no record is found, return null
        if($activeRefreshTokenRecords->isEmpty()) {
            return null;
        }

        // Check if the provided plain refresh token matches any of the hashed tokens in the database
        foreach($activeRefreshTokenRecords as $record) {
            if(Hash::check($plainRefreshToken, $record->token_hash)) {
                return $record;
            }
        }

        return null;
    }

    public static function revokeRefreshToken(int|string $userId, string $plainRefreshToken): bool {
        // Get the refresh token record from the database for this user and the provided plain refresh token
        $refreshTokenRecord = self::findRefreshTokenRecordByUserAndToken($userId, $plainRefreshToken);

        // If no record is found, return false
        if(!$refreshTokenRecord) {
            return false;
        }

        // Revoke the refresh token by setting the revoked_at timestamp to now
        $refreshTokenRecord->revoked_at = now();
        $refreshTokenRecord->save();

        return true;
    }

    public static function getActiveSessions(int|string $userId): array {
        return self::where('fk_user_id', $userId)
            ->where('expires_at', '>', now())
            ->where('revoked_at', null)
            ->get(['id', 'ip_address', 'user_agent', 'expires_at'])
            ->toArray();
    }

    public static function revokeActiveSession(int|string $userId, int|string $refreshTokenId): bool {
        $activeSessionRecord = self::where('fk_user_id', $userId)
            ->where('id', $refreshTokenId)
            ->where('expires_at', '>', now())
            ->where('revoked_at', null)
            ->first();

        if(empty($activeSessionRecord)) {
            return false;
        }
        
        $updated = $activeSessionRecord->update([
            'revoked_at' => now()
        ]);

        return $updated;
    }
}
