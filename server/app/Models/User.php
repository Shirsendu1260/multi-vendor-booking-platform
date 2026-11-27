<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\UserRoleEnum;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

#[Fillable(['name', 'email', 'password', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements JWTSubject // JWT-aware
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRoleEnum::class,
        ];
    }


    // JWT methods //

    // This tells JWT "what's the unique ID that represents this user inside the token." $this->getKey() 
    // is Laravel's built-in way of saying "give me this model's primary key," which for us is just the 
    // id column. So the token will carry user's ID inside it.
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // "claims" are extra pieces of information we can optionally stuff into the token besides the ID. 
    // Right now we return an empty array, meaning no extra data.
    public function getJWTCustomClaims()
    {
        return [];
    }


    // Relationships //

    public function vendorProfile() {
        return $this->hasOne(VendorProfile::class, 'fk_vendor_id');
    }

    public function approvedVendorProfiles() {
        // This admin user has approved many vendor profiles, so we use hasMany. 
        // The foreign key in the vendor_profiles table that points to this admin user is approved_by.
        return $this->hasMany(VendorProfile::class, 'approved_by');
    }

    public function bookings() {
        return $this->hasMany(Booking::class, 'fk_customer_id');
    }

    public function reviews() {
        return $this->hasMany(Review::class, 'fk_customer_id');
    }

    public function refreshTokens() {
        return $this->hasMany(RefreshToken::class, 'fk_user_id');
    }


    // Model functions //

    public static function existsByEmail(string $email): bool {
        return self::where('email', $email)->exists();
    }
}
