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
        Schema::create('assignment_group_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assignment_id')
                ->constrained('assignments')
                ->restrictOnDelete();

            $table->foreignId('assignment_group_id');

            $table->foreignId('student_id')
                ->constrained('students')
                ->restrictOnDelete();

            $table->timestamps();

            // Satu mahasiswa hanya boleh masuk satu kelompok untuk setiap tugas.
            $table->unique(
                ['assignment_id', 'student_id'],
                'agm_assignment_student_unique'
            );

            // Memastikan kelompok memang milik tugas yang sama.
            $table->foreign(
                ['assignment_id', 'assignment_group_id'],
                'agm_assignment_group_fk'
            )->references(
                ['assignment_id', 'id']
            )->on('assignment_groups')
                ->cascadeOnDelete();

            $table->index(['assignment_group_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_group_members');
    }
};
