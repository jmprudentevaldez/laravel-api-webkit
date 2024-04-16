<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('first_name')->fulltext();
            $table->string('last_name')->fulltext();
            $table->string('middle_name')->nullable()->fulltext();
            $table->string('ext_name')->nullable();
            $table->string('mobile_number')->unique()->nullable();
            $table->string('telephone_number')->nullable();
            $table->enum('sex', ['male', 'female'])->nullable();
            $table->date('birthday')->nullable();
            $table->string('profile_picture_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
