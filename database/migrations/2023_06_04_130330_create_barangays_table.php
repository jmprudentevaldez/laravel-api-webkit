<?php

use App\Enums\BarangayClassification;
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
        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->string('code_correspondence')->unique();
            $table->foreignId('city_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('old_name')->nullable();
            $table->string('geo_level');
            $table->enum('classification',
                ConversionHelper::convertEnumToArray(BarangayClassification::class)
            )->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
