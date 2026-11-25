<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['fk_vendor_id', 'business_name'])]
class VendorProfile extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }


    // Relationships //

    public function vendor() {
        return $this->belongsTo(User::class, 'fk_vendor_id');
    }

    public function approvedAdmin() {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function services() {
        return $this->hasMany(Service::class, 'fk_vendor_profile_id');
    }
}
