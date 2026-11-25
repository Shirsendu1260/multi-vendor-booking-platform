<?php

namespace App\Models;

use App\ListingCategoryEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['fk_vendor_profile_id', 'title', 'description', 'category', 'location', 'price_per_slot'])]
class Service extends Model
{
    use SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => ListingCategoryEnum::class,
            'price_per_slot' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }


    // Realationships //

    public function vendorProfile() {
        return $this->belongsTo(VendorProfile::class, 'fk_vendor_profile_id');
    }

    public function slots() {
        return $this->hasMany(Slot::class, 'fk_service_id');
    }

    public function reviews() {
        return $this->hasMany(Review::class, 'fk_service_id');
    }
}
