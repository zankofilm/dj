<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seller_payout_destinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->restrictOnDelete();
            $table->char('country_code', 2);
            $table->char('currency', 3);
            $table->string('bank_name', 150)->nullable();
            $table->string('account_holder_name', 190);
            $table->text('account_identifier_encrypted');
            $table->string('verification_status', 25)->default('pending')->index();
            $table->boolean('is_default')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['seller_id', 'currency', 'is_default']);
        });

        Schema::create('seller_settlements', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('seller_id')->constrained()->restrictOnDelete();
            $table->foreignId('seller_order_id')->unique()->constrained()->restrictOnDelete();
            $table->char('currency', 3);
            $table->unsignedBigInteger('gross_minor');
            $table->unsignedBigInteger('commission_minor');
            $table->bigInteger('adjustments_minor')->default(0);
            $table->unsignedBigInteger('net_payable_minor');
            $table->string('status', 25)->default('pending')->index();
            $table->timestamp('eligible_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('ledger_event_key', 150)->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['seller_id', 'currency', 'status']);
        });

        Schema::create('seller_payouts', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('seller_id')->constrained()->restrictOnDelete();
            $table->foreignId('payout_destination_id')->constrained('seller_payout_destinations')->restrictOnDelete();
            $table->char('currency', 3);
            $table->unsignedBigInteger('amount_minor');
            $table->string('status', 25)->default('requested')->index();
            $table->string('provider_key', 80)->nullable();
            $table->string('provider_reference', 190)->nullable()->index();
            $table->string('idempotency_key', 100)->unique();
            $table->timestamp('requested_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('failure_code', 100)->nullable();
            $table->string('failure_message', 500)->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['seller_id', 'currency', 'status']);
        });

        Schema::create('seller_payout_settlement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_payout_id')->constrained('seller_payouts')->restrictOnDelete();
            $table->foreignId('seller_settlement_id')->constrained('seller_settlements')->restrictOnDelete();
            $table->unsignedBigInteger('allocated_minor');
            $table->timestamps();
            $table->unique(['seller_payout_id', 'seller_settlement_id'], 'payout_settlement_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_payout_settlement');
        Schema::dropIfExists('seller_payouts');
        Schema::dropIfExists('seller_settlements');
        Schema::dropIfExists('seller_payout_destinations');
    }
};
