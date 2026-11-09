<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->foreignId('owner_user_id')->nullable()->after('public_id')
                ->constrained('users')->restrictOnDelete();
            $table->string('registration_number')->nullable()->index();
            $table->char('country_code', 2)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 40)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->index(['owner_user_id', 'status']);
        });

        Schema::create('seller_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 60);
            $table->string('storage_path');
            $table->string('original_name');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('file_size');
            $table->string('status', 20)->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->index(['seller_id', 'document_type']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 120)->index();
            $table->string('subject_type', 120);
            $table->string('subject_id', 100);
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['subject_type', 'subject_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('seller_documents');

        Schema::table('sellers', function (Blueprint $table) {
            $table->dropIndex(['owner_user_id', 'status']);
            $table->dropConstrainedForeignId('owner_user_id');
            $table->dropColumn([
                'registration_number', 'country_code', 'contact_email',
                'contact_phone', 'reviewed_at', 'rejection_reason',
            ]);
            $table->dropConstrainedForeignId('reviewed_by');
        });
    }
};
