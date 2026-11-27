<?php

use App\BookingStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->foreignId('fk_slot_id')->unique()->constrained('slots')->restrictOnDelete();
            $table->foreignId('fk_customer_id')
                ->constrained('users')
                ->restrictOnDelete()
                ->comment("FK with 'users'");
            $table->string('status', 30)
                ->default(BookingStatusEnum::PENDING->value)
                ->comment('pending, confirmed, completed, cancelled, refunded, disputed');
            
            $table->decimal('amount', 10, 2);
            // We intentionally store the price here even though services.price_per_slot already has it.
            // This ('bookings.amount') preserves the price at the time of booking, so old bookings still show the original
            // old amount paid even if the vendor changes their price ('services.price_per_slot) later.

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
