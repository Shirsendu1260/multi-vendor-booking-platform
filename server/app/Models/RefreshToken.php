<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

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
}
