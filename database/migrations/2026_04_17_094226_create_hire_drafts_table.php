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
        Schema::create('hire_drafts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->json('data')->nullable();
            $table->unsignedTinyInteger('step')->default(1);
            $table->string('status')->default('draft'); // draft, submitted
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hire_drafts');
    }
};
