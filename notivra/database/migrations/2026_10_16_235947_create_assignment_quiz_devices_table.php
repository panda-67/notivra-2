<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_quiz_devices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assignment_id')
                ->constrained('assignments')
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->char('device_token_hash', 64);

            $table->timestamp('first_used_at');
            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();

            // Satu hash perangkat hanya boleh terdaftar sekali
            // pada assignment yang sama.
            $table->unique(
                ['assignment_id', 'device_token_hash'],
                'assignment_quiz_devices_assignment_device_unique'
            );

            // Mempercepat pencarian perangkat milik mahasiswa
            // pada assignment tertentu.
            $table->index(
                ['assignment_id', 'student_id'],
                'assignment_quiz_devices_assignment_student_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_quiz_devices');
    }
};
