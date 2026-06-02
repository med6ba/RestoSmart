<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->boolean('is_occupied')->default(false)->index()->after('is_active');
            $table->foreignId('occupied_order_id')->nullable()->after('is_occupied');
            $table->timestamp('occupied_at')->nullable()->after('occupied_order_id');

            $table->foreign('occupied_order_id', 'rs_restaurant_tables_occupied_order_fk')
                ->references('id')
                ->on('orders')
                ->nullOnDelete();
        });

        DB::table('orders')
            ->selectRaw('restaurant_table_id, max(id) as order_id, max(created_at) as occupied_at')
            ->where('type', 'local')
            ->whereNotNull('restaurant_table_id')
            ->whereNotIn('status', ['collected', 'cancelled'])
            ->groupBy('restaurant_table_id')
            ->get()
            ->each(function (object $order): void {
                DB::table('restaurant_tables')
                    ->where('id', $order->restaurant_table_id)
                    ->update([
                        'is_occupied' => true,
                        'occupied_order_id' => $order->order_id,
                        'occupied_at' => $order->occupied_at,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->dropForeign('rs_restaurant_tables_occupied_order_fk');
            $table->dropColumn(['is_occupied', 'occupied_order_id', 'occupied_at']);
        });
    }
};
