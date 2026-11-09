<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('theme_packages', function (Blueprint $table) {
            $table->id();
            $table->string('theme_id')->unique();
            $table->string('version');
            $table->string('status', 30)->default('uploaded')->index();
            $table->string('artifact_path');
            $table->string('sha256', 64);
            $table->json('manifest');
            $table->timestamps();
        });

        Schema::create('extension_packages', function (Blueprint $table) {
            $table->id();
            $table->string('extension_id')->unique();
            $table->string('version');
            $table->string('trust_level', 20)->default('quarantined');
            $table->string('status', 30)->default('uploaded')->index();
            $table->string('artifact_path');
            $table->string('sha256', 64);
            $table->json('manifest');
            $table->timestamps();
        });

        Schema::create('page_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('page_type', 60);
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedInteger('schema_version')->default(1);
            $table->json('document');
            $table->unsignedInteger('revision')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_documents');
        Schema::dropIfExists('extension_packages');
        Schema::dropIfExists('theme_packages');
    }
};
