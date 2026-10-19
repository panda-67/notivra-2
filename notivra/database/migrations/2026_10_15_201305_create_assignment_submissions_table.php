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
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assignment_id')
                ->constrained('assignments')
                ->restrictOnDelete();

            $table->foreignId('assignment_group_id')->unique();

            $table->foreignId('uploaded_by_member_id')
                ->nullable()
                ->constrained('assignment_group_members')
                ->restrictOnDelete();

            // Lokasi relatif di private storage, bukan URL publik.
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type', 127);
            $table->unsignedBigInteger('file_size');

            // Fingerprint berkas dan konten DOCX.
            $table->char('sha256', 64);
            $table->char('content_fingerprint', 64)->nullable();
            $table->char('structure_fingerprint', 64)->nullable();
            $table->char('metadata_fingerprint', 64)->nullable();

            $table->enum('duplicate_status', [
                'pending',
                'clear',
                'flagged',
                'under_review',
                'confirmed_duplicate',
            ])->default('pending');

            // Detail analisis yang dapat dijelaskan dan ditinjau.
            $table->json('metadata')->nullable();
            $table->json('duplicate_findings')->nullable();

            $table->timestamp('submitted_at');

            $table->timestamps();

            // Memastikan kelompok memang berasal dari tugas tersebut.
            $table->foreign(
                ['assignment_id', 'assignment_group_id'],
                'as_assignment_group_fk'
            )->references(
                ['assignment_id', 'id']
            )->on('assignment_groups')
                ->restrictOnDelete();

            $table->index(['assignment_id', 'duplicate_status']);
            $table->index('sha256');
            $table->index('content_fingerprint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
