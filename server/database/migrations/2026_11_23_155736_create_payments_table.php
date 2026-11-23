<?php

use App\PaymentStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_booking_id')
                ->unique()
                ->constrained('bookings')
                ->restrictOnDelete(); // 1:1 for bookings-payments
            $table->string('stripe_payment_intent_id', 500);
            $table->string('stripe_customer_id', 500);
            $table->string('stripe_payment_method_id', 500);
            $table->string('status', 30)
                ->default(PaymentStatusEnum::AUTHORIZED->value)
                ->comment('authorized, captured, refunded, partially_refunded, failed');
            $table->decimal('amount', 10, 2);
            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
