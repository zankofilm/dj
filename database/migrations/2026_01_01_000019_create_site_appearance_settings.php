<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_appearance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 80)->unique();
            $table->json('value');
            $table->timestamps();
        });

        $defaults = [
            'brand' => ['fa'=>'بازارگاه','ku'=>'بازاڕگا','en'=>'Marketplace'],
            'announcement' => ['fa'=>'به بازارگاه خوش آمدید؛ خرید از فروشندگان معتبر','ku'=>'بەخێربێن بۆ بازاڕگا','en'=>'Welcome to Marketplace — shop from trusted sellers'],
            'footer_tagline' => ['fa'=>'بازار چندفروشنده‌ای برای خریدی مطمئن و آسان','ku'=>'بازاڕێکی فرەفرۆشیار بۆ کڕینێکی ئاسان و دڵنیا','en'=>'A multi-vendor marketplace for easy, trusted shopping'],
            'footer_copyright' => ['fa'=>'تمام حقوق محفوظ است.','ku'=>'هەموو مافەکان پارێزراون.','en'=>'All rights reserved.'],
            'contact' => ['email'=>'','phone'=>'','instagram'=>'','telegram'=>''],
        ];
        foreach ($defaults as $key => $value) {
            DB::table('site_appearance_settings')->insert([
                'setting_key'=>$key,
                'value'=>json_encode($value, JSON_UNESCAPED_UNICODE),
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_appearance_settings');
    }
};
