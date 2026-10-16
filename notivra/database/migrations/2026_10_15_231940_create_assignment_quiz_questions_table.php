<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_quiz_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assignment_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->text('question');

            $table->text('option_a');
            $table->text('option_b');
            $table->text('option_c');
            $table->text('option_d');

            $table->char('correct_option', 1);

            $table->unsignedSmallInteger('position');

            $table->timestamps();

            $table->unique(
                ['assignment_id', 'position'],
                'assignment_quiz_questions_assignment_position_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_quiz_questions');
    }
};
