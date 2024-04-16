<?php

use App\Enums\MunicipalClassification;
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
        Schema::create('cities', function (Blueprint $table) {

            $table->id();
            $table->foreignId('province_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code_correspondence')->unique();
            $table->string('code');
            $table->string('name');
            $table->string('old_name')->nullable();
            $table->enum('classification',
                ConversionHelper::convertEnumToArray(MunicipalClassification::class)
            )->index();
            $table->string('city_class')->nullable();
            $table->string('income_classification')->nullable();
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
