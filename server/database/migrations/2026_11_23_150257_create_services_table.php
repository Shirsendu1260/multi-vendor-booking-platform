<?php

use App\ListingCategoryEnum;
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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->foreignId('fk_vendor_profile_id')
                ->constrained('vendor_profiles')
                ->restrictOnDelete()
                ->comment('User\'s id is fetchable;');
            $table->string('title', 255);
            $table->text('description');
            $table->string('category', 30)
                ->comment('venue, photography, catering');
            $table->string('location', 500);
            $table->decimal('price_per_slot', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
