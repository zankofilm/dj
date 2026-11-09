<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_homepage_featured')->default(false)->index();
            $table->unsignedSmallInteger('homepage_sort_order')->default(0);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_homepage_featured')->default(false)->index();
            $table->unsignedSmallInteger('homepage_sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_homepage_featured', 'homepage_sort_order']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['is_homepage_featured', 'homepage_sort_order']);
        });
    }
};
