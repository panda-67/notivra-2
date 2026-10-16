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
        Schema::create('assignment_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assignment_id')
                ->constrained('assignments')
                ->restrictOnDelete();

            $table->string('group_name')->nullable();
            $table->string('reference_code', 32)->unique();

            $table->enum('status', [
                'draft',
                'verification_in_progress',
                'ready_for_upload',
                'submitted',
                'under_review',
                'accepted',
                'rejected',
            ])->default('draft');

            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            // Diperlukan sebagai target composite foreign key anggota.
            $table->unique(
                ['assignment_id', 'id'],
                'assignment_groups_assignment_id_id_unique'
            );

            $table->index(['assignment_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_groups');
    }
};
