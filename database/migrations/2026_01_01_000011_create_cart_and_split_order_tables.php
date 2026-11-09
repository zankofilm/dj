<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->char('currency', 3)->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
            $table->unique(['cart_id', 'variant_id']);
        });

        Schema::create('seller_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained()->restrictOnDelete();
            $table->string('status', 30)->default('pending_payment')->index();
            $table->char('currency', 3);
            $table->unsignedBigInteger('subtotal_minor');
            $table->unsignedBigInteger('shipping_minor')->default(0);
            $table->unsignedBigInteger('discount_minor')->default(0);
            $table->unsignedBigInteger('total_minor');
            $table->json('fulfillment_snapshot')->nullable();
            $table->timestamps();
            $table->unique(['order_id', 'seller_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('seller_order_id')->nullable()->after('order_id')
                ->constrained('seller_orders')->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->after('product_id')
                ->constrained('product_variants')->nullOnDelete();
            $table->string('sku_snapshot')->nullable()->after('product_title_snapshot');
            $table->index(['seller_order_id', 'seller_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('public_id')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('placed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('placed_at');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['seller_order_id', 'seller_id']);
            $table->dropConstrainedForeignId('seller_order_id');
            $table->dropConstrainedForeignId('variant_id');
            $table->dropColumn('sku_snapshot');
        });

        Schema::dropIfExists('seller_orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
