<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_documents', function (Blueprint $table) {
            $table->id();
            $table->string('doc_no', 20)->unique();
            $table->string('template_key', 120);
            $table->string('form_type', 40);
            $table->string('title');
            $table->json('content')->nullable();
            $table->foreignId('writer_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'rejected', 'completed'])->default('pending');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });

        Schema::create('approval_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('approval_documents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('line_type', ['approval', 'cooperation']);
            $table->unsignedTinyInteger('line_order')->default(1);
            $table->enum('status', ['pending', 'approved', 'rejected', 'confirmed'])->default('pending');
            $table->timestamp('acted_at')->nullable();
            $table->text('action_comment')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'line_type', 'line_order'], 'approval_lines_doc_type_order_unique');
        });

        Schema::create('approval_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('approval_documents')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        Schema::create('approval_opinions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('approval_documents')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['reject', 'comment'])->default('comment');
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_opinions');
        Schema::dropIfExists('approval_attachments');
        Schema::dropIfExists('approval_lines');
        Schema::dropIfExists('approval_documents');
    }
};
