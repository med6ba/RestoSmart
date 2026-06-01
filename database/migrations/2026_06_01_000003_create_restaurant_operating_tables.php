<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreign('tenant_id', 'rs_categories_tenant_fk')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreign('tenant_id', 'rs_menu_items_tenant_fk')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('category_id');
            $table->foreign('category_id', 'rs_menu_items_category_fk')->references('id')->on('categories')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('price_cents');
            $table->unsignedInteger('prep_minutes')->default(12);
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('stock_tracked')->default(true);
            $table->timestamps();
        });

        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreign('tenant_id', 'rs_ingredients_tenant_fk')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('unit')->default('unit');
            $table->decimal('current_stock', 10, 2)->default(0);
            $table->decimal('low_stock_threshold', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('ingredient_menu_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id');
            $table->foreignId('menu_item_id');
            $table->decimal('quantity_required', 10, 2)->default(1);

            $table->foreign('ingredient_id', 'rs_ingredient_menu_ingredient_fk')->references('id')->on('ingredients')->cascadeOnDelete();
            $table->foreign('menu_item_id', 'rs_ingredient_menu_item_fk')->references('id')->on('menu_items')->cascadeOnDelete();
            $table->unique(['ingredient_id', 'menu_item_id']);
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreign('tenant_id', 'rs_customer_addresses_tenant_fk')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('user_id');
            $table->foreign('user_id', 'rs_customer_addresses_user_fk')->references('id')->on('users')->cascadeOnDelete();
            $table->string('label')->default('Home');
            $table->text('address');
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreign('tenant_id', 'rs_orders_tenant_fk')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('public_code')->unique();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('driver_id')->nullable();
            $table->foreignId('customer_address_id')->nullable();
            $table->foreign('user_id', 'rs_orders_user_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('driver_id', 'rs_orders_driver_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('customer_address_id', 'rs_orders_customer_address_fk')->references('id')->on('customer_addresses')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('type')->default('delivery')->index();
            $table->string('status')->default('received')->index();
            $table->string('payment_status')->default('pending')->index();
            $table->unsignedInteger('subtotal_cents')->default(0);
            $table->unsignedInteger('delivery_fee_cents')->default(0);
            $table->unsignedInteger('total_cents')->default(0);
            $table->text('kitchen_notes')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreign('tenant_id', 'rs_order_items_tenant_fk')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('order_id');
            $table->foreignId('menu_item_id')->nullable();
            $table->foreign('order_id', 'rs_order_items_order_fk')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('menu_item_id', 'rs_order_items_menu_item_fk')->references('id')->on('menu_items')->nullOnDelete();
            $table->string('name');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_price_cents');
            $table->unsignedInteger('total_price_cents');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreign('tenant_id', 'rs_deliveries_tenant_fk')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('order_id')->unique();
            $table->foreignId('driver_id')->nullable();
            $table->foreign('order_id', 'rs_deliveries_order_fk')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('driver_id', 'rs_deliveries_driver_fk')->references('id')->on('users')->nullOnDelete();
            $table->string('status')->default('waiting')->index();
            $table->string('route_summary')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreign('tenant_id', 'rs_stock_movements_tenant_fk')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('ingredient_id');
            $table->foreignId('order_id')->nullable();
            $table->foreign('ingredient_id', 'rs_stock_movements_ingredient_fk')->references('id')->on('ingredients')->cascadeOnDelete();
            $table->foreign('order_id', 'rs_stock_movements_order_fk')->references('id')->on('orders')->nullOnDelete();
            $table->string('type')->index();
            $table->decimal('quantity', 10, 2);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreign('tenant_id', 'rs_notifications_tenant_fk')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable();
            $table->foreign('user_id', 'rs_notifications_user_fk')->references('id')->on('users')->cascadeOnDelete();
            $table->string('role')->nullable()->index();
            $table->string('type')->default('info')->index();
            $table->string('title');
            $table->text('body')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('ingredient_menu_item');
        Schema::dropIfExists('ingredients');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('categories');
    }
};
