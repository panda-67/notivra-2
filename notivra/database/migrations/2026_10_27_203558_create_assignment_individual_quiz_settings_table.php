<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_individual_quiz_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assignment_id')
                ->unique()
                ->constrained('assignments')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('duration_minutes')
                ->default(50);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_individual_quiz_settings');
    }
};
