<?php

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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_profile_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('home_address')->nullable();
            $table->foreignId('barangay_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('province_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('postal_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
