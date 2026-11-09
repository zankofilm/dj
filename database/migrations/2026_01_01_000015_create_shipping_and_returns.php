<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('seller_order_id')->constrained()->restrictOnDelete();
            $table->string('provider_key', 80)->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->string('tracking_number', 190)->nullable()->index();
            $table->string('label_reference', 190)->nullable();
            $table->unsignedBigInteger('shipping_minor')->default(0);
            $table->char('currency', 3);
            $table->json('recipient_snapshot');
            $table->json('provider_metadata')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('failure_code', 100)->nullable();
            $table->string('failure_message', 500)->nullable();
            $table->timestamps();
            $table->index(['seller_order_id', 'status']);
        });

        Schema::create('shipment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->restrictOnDelete();
            $table->string('provider_event_id', 190)->nullable();
            $table->string('event_type', 80);
            $table->string('status', 30);
            $table->string('description', 500)->nullable();
            $table->string('location_label', 190)->nullable();
            $table->char('payload_hash', 64)->nullable();
            $table->timestamp('occurred_at');
            $table->unique(['shipment_id', 'provider_event_id'], 'shipment_event_provider_unique');
            $table->index(['shipment_id', 'occurred_at']);
        });

        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('order_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('seller_order_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('reason_code', 60);
            $table->text('customer_note')->nullable();
            $table->string('status', 30)->default('requested')->index();
            $table->string('resolution_type', 30)->nullable();
            $table->unsignedBigInteger('requested_refund_minor')->default(0);
            $table->unsignedBigInteger('approved_refund_minor')->nullable();
            $table->char('currency', 3);
            $table->text('decision_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['customer_user_id', 'status']);
            $table->index(['seller_order_id', 'status']);
        });

        Schema::create('return_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_request_id')->constrained()->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 80);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['return_request_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_events');
        Schema::dropIfExists('return_requests');
        Schema::dropIfExists('shipment_events');
        Schema::dropIfExists('shipments');
    }
};
