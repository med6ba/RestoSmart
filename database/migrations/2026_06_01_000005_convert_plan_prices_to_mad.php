<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $prices = [
            'starter' => 29000,
            'pro' => 79000,
            'business' => 149000,
        ];

        foreach ($prices as $slug => $price) {
            DB::table('plans')->where('slug', $slug)->update([
                'monthly_price_cents' => $price,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $prices = [
            'starter' => 2900,
            'pro' => 7900,
            'business' => 14900,
        ];

        foreach ($prices as $slug => $price) {
            DB::table('plans')->where('slug', $slug)->update([
                'monthly_price_cents' => $price,
                'updated_at' => now(),
            ]);
        }
    }
};
