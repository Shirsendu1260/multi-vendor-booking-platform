<?php

namespace App\Models;

use App\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'fk_booking_id',
    'stripe_payment_intent_id',
    'stripe_customer_id',
    'stripe_payment_method_id',
    'amount',
    'refunded_amount',
])]
class Payment extends Model
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
            'status' => PaymentStatusEnum::class,
            'amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
        ];
    }


    // Relationships //

    public function booking() {
        return $this->belongsTo(Booking::class, 'fk_booking_id');
    }
}
