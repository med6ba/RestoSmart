<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreign('tenant_id', 'rs_restaurant_tables_tenant_fk')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('code');
            $table->string('qr_token')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('restaurant_table_id')->nullable()->after('customer_address_id');
            $table->timestamp('collected_at')->nullable()->after('delivered_at');

            $table->foreign('restaurant_table_id', 'rs_orders_restaurant_table_fk')
                ->references('id')
                ->on('restaurant_tables')
                ->nullOnDelete();
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->decimal('restaurant_latitude', 10, 7)->nullable()->after('route_summary');
            $table->decimal('restaurant_longitude', 10, 7)->nullable()->after('restaurant_latitude');
            $table->decimal('destination_latitude', 10, 7)->nullable()->after('restaurant_longitude');
            $table->decimal('destination_longitude', 10, 7)->nullable()->after('destination_latitude');
            $table->decimal('driver_latitude', 10, 7)->nullable()->after('destination_longitude');
            $table->decimal('driver_longitude', 10, 7)->nullable()->after('driver_latitude');
            $table->timestamp('last_location_at')->nullable()->after('driver_longitude');
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn([
                'restaurant_latitude',
                'restaurant_longitude',
                'destination_latitude',
                'destination_longitude',
                'driver_latitude',
                'driver_longitude',
                'last_location_at',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('rs_orders_restaurant_table_fk');
            $table->dropColumn(['restaurant_table_id', 'collected_at']);
        });

        Schema::dropIfExists('restaurant_tables');
    }
};
