<?php

namespace App\Models;

use App\RatingEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['fk_booking_id', 'fk_customer_id', 'fk_service_id', 'rating', 'comment'])]
class Review extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => RatingEnum::class,
        ];
    }


    // Relationships //

    public function booking() {
        return $this->belongsTo(Booking::class, 'fk_booking_id');
    }

    public function customer() {
        return $this->belongsTo(User::class, 'fk_customer_id');
    }

    public function service() {
        return $this->belongsTo(Service::class, 'fk_service_id');
    }
}
