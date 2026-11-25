<?php

namespace App\Models;

use App\SlotStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['fk_service_id', 'start_time', 'end_time'])]
class Slot extends Model
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
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'status' => SlotStatusEnum::class,
        ];
    }


    // Relationships //

    public function service() {
        return $this->belongsTo(Service::class, 'fk_service_id');
    }

    public function booking() {
        return $this->hasOne(Booking::class, 'fk_slot_id');
    }
}
