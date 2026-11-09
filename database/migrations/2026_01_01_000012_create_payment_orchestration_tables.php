<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('payments',function(Blueprint $t){
   $t->id(); $t->uuid('public_id')->unique(); $t->foreignId('order_id')->constrained()->restrictOnDelete();
   $t->foreignId('user_id')->constrained()->restrictOnDelete(); $t->string('provider_key',80);
   $t->string('status',30)->default('created')->index(); $t->char('currency',3); $t->unsignedBigInteger('amount_minor');
   $t->string('idempotency_key',100)->unique(); $t->string('provider_reference',190)->nullable()->index();
   $t->text('checkout_url')->nullable(); $t->timestamp('expires_at')->nullable(); $t->timestamp('paid_at')->nullable();
   $t->string('failure_code',100)->nullable(); $t->string('failure_message',500)->nullable(); $t->json('metadata')->nullable();
   $t->timestamps(); $t->index(['order_id','status']);
  });
  Schema::create('payment_attempts',function(Blueprint $t){
   $t->id(); $t->foreignId('payment_id')->constrained()->restrictOnDelete(); $t->unsignedInteger('attempt_number');
   $t->string('status',30)->index(); $t->string('provider_reference',190)->nullable()->index();
   $t->char('request_fingerprint',64)->nullable(); $t->string('response_code',100)->nullable();
   $t->string('failure_code',100)->nullable(); $t->string('failure_message',500)->nullable();
   $t->timestamp('started_at'); $t->timestamp('finished_at')->nullable(); $t->timestamps();
   $t->unique(['payment_id','attempt_number']);
  });
  Schema::create('payment_webhook_events',function(Blueprint $t){
   $t->id(); $t->string('provider_key',80); $t->string('provider_event_id',190); $t->string('event_type',100);
   $t->char('payload_hash',64); $t->boolean('signature_valid')->default(false); $t->string('processing_status',30)->default('received')->index();
   $t->timestamp('received_at'); $t->timestamp('processed_at')->nullable(); $t->string('failure_message',500)->nullable();
   $t->timestamps(); $t->unique(['provider_key','provider_event_id']);
  });
 }
 public function down(): void { Schema::dropIfExists('payment_webhook_events'); Schema::dropIfExists('payment_attempts'); Schema::dropIfExists('payments'); }
};
