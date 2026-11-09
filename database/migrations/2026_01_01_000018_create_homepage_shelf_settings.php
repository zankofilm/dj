<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('homepage_shelf_settings', function (Blueprint $table) {
            $table->id();
            $table->string('shelf_key', 40)->unique();
            $table->json('translations');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('item_limit')->default(12);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedSmallInteger('motion_speed')->default(35);
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            ['latest', ['fa'=>['title'=>'جدیدترین محصولات','eyebrow'=>'JUST ARRIVED'],'ku'=>['title'=>'نوێترین بەرهەمەکان','eyebrow'=>'JUST ARRIVED'],'en'=>['title'=>'Latest products','eyebrow'=>'JUST ARRIVED']], 1, 12, 1, 35],
            ['popular', ['fa'=>['title'=>'محبوب‌ترین‌ها','eyebrow'=>'BEST SELLERS'],'ku'=>['title'=>'زۆرترین داواکراوەکان','eyebrow'=>'BEST SELLERS'],'en'=>['title'=>'Most popular','eyebrow'=>'BEST SELLERS']], 1, 12, 2, 35],
            ['cheapest', ['fa'=>['title'=>'ارزان‌ترین‌ها','eyebrow'=>'BEST PRICE'],'ku'=>['title'=>'هەرزانترینەکان','eyebrow'=>'BEST PRICE'],'en'=>['title'=>'Lowest prices','eyebrow'=>'BEST PRICE']], 1, 12, 3, 35],
            ['discount', ['fa'=>['title'=>'تخفیف‌ها','eyebrow'=>'SPECIAL OFFERS'],'ku'=>['title'=>'داشکاندنەکان','eyebrow'=>'SPECIAL OFFERS'],'en'=>['title'=>'Special offers','eyebrow'=>'SPECIAL OFFERS']], 1, 12, 4, 35],
        ];
        foreach ($defaults as [$key, $translations, $active, $limit, $order, $speed]) {
            DB::table('homepage_shelf_settings')->insert([
                'shelf_key'=>$key, 'translations'=>json_encode($translations, JSON_UNESCAPED_UNICODE),
                'is_active'=>$active, 'item_limit'=>$limit, 'sort_order'=>$order,
                'motion_speed'=>$speed, 'created_at'=>$now, 'updated_at'=>$now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_shelf_settings');
    }
};
