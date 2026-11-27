<?php

use App\RatingEnum;
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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->foreignId('fk_booking_id')->unique()->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('fk_customer_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment("FK with 'users'");
            $table->foreignId('fk_service_id')->constrained('services')->cascadeOnDelete();
            $table->integer('rating')->comment('Ratings are from 1 to 5');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
