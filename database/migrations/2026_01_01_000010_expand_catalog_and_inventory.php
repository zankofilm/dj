<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->index();
            $table->unsignedInteger('sort_order')->default(0);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('product_type', 20)->default('physical')->index();
            $table->string('condition', 20)->default('new')->index();
            $table->string('brand_name')->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 100)->unique();
            $table->string('barcode', 100)->nullable()->index();
            $table->string('title')->nullable();
            $table->json('option_values')->nullable();
            $table->unsignedBigInteger('price_minor')->nullable();
            $table->unsignedBigInteger('compare_at_price_minor')->nullable();
            $table->char('currency', 3);
            $table->boolean('track_inventory')->default(true);
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
        });

        Schema::create('product_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('storage_path');
            $table->json('alt_translations')->nullable();
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('file_size');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->index(['product_id', 'sort_order']);
        });

        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->unique()->constrained('product_variants')->cascadeOnDelete();
            $table->unsignedBigInteger('on_hand')->default(0);
            $table->unsignedBigInteger('reserved')->default(0);
            $table->unsignedBigInteger('low_stock_threshold')->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->check('reserved <= on_hand');
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->string('movement_type', 30);
            $table->bigInteger('quantity_delta');
            $table->string('reference_type', 80)->nullable();
            $table->string('reference_id', 100)->nullable();
            $table->string('reason', 500)->nullable();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['variant_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_balances');
        Schema::dropIfExists('product_media');
        Schema::dropIfExists('product_variants');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['product_type', 'condition', 'brand_name', 'published_at']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['status', 'sort_order']);
        });
    }
};
