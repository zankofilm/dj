<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('payment_webhook_events', function (Blueprint $table) {
            $table->string('payment_reference', 190)->nullable()->after('event_type');
            $table->timestamp('processing_started_at')->nullable()->after('received_at');
            $table->index(['provider_key', 'payment_reference']);
        });
    }
    public function down(): void {
        Schema::table('payment_webhook_events', function (Blueprint $table) {
            $table->dropIndex(['provider_key', 'payment_reference']);
            $table->dropColumn(['payment_reference', 'processing_started_at']);
        });
    }
};
