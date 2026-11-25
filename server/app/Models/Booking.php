<?php

namespace App\Models;

use App\BookingStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['fk_slot_id', 'fk_customer_id', 'amount'])]
class Booking extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BookingStatusEnum::class,
            'amount' => 'decimal:2',
        ];
    }


    // Relationships //

    public function slot() {
        return $this->belongsTo(Slot::class, 'fk_slot_id');
    }

    public function customer() {
        return $this->belongsTo(User::class, 'fk_customer_id');
    }

    public function payment() {
        return $this->hasOne(Payment::class, 'fk_booking_id');
    }
}
