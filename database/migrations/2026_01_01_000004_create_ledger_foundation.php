<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Reserved migration slot.
 *
 * The initial ledger prototype was superseded by
 * 2026_01_01_000013_create_double_entry_ledger.php.
 * Keeping this migration as a no-op preserves migration ordering without
 * creating the obsolete ledger_accounts table twice.
 */
return new class extends Migration {
    public function up(): void
    {
        // Intentionally empty: the canonical double-entry ledger is created in migration 000013.
    }

    public function down(): void
    {
        // Intentionally empty.
    }
};
