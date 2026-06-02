<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_messages', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreign('tenant_id', 'rs_delivery_messages_tenant_fk')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('order_id')->index();
            $table->foreignId('delivery_id')->index();
            $table->foreignId('sender_id')->nullable()->index();
            $table->foreignId('receiver_id')->nullable()->index();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id', 'rs_delivery_messages_order_fk')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('delivery_id', 'rs_delivery_messages_delivery_fk')->references('id')->on('deliveries')->cascadeOnDelete();
            $table->foreign('sender_id', 'rs_delivery_messages_sender_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('receiver_id', 'rs_delivery_messages_receiver_fk')->references('id')->on('users')->nullOnDelete();
            $table->index('created_at', 'rs_delivery_messages_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_messages');
    }
};
