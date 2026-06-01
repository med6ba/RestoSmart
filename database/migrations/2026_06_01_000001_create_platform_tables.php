<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('monthly_price_cents')->default(0);
            $table->unsignedInteger('max_staff')->default(3);
            $table->unsignedInteger('max_active_orders')->default(25);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('restaurant_applications', function (Blueprint $table) {
            $table->id();
            $table->string('restaurant_name');
            $table->string('desired_slug')->unique();
            $table->string('owner_name');
            $table->string('owner_email')->index();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('plan_id')->nullable();
            $table->foreign('plan_id', 'rs_platform_applications_plan_fk')
                ->references('id')
                ->on('plans')
                ->nullOnDelete();
            $table->string('status')->default('pending')->index();
            $table->string('tenant_id')->nullable()->index();
            $table->text('decision_note')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreignId('plan_id');
            $table->foreign('plan_id', 'rs_platform_subscriptions_plan_fk')
                ->references('id')
                ->on('plans');
            $table->string('status')->default('trial')->index();
            $table->timestamp('trial_started_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_started_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('billing_histories', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreignId('plan_id')->nullable();
            $table->foreign('plan_id', 'rs_platform_billing_plan_fk')
                ->references('id')
                ->on('plans')
                ->nullOnDelete();
            $table->unsignedInteger('amount_cents')->default(0);
            $table->string('status')->default('trial_credit')->index();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('platform_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->string('type')->default('info')->index();
            $table->string('title');
            $table->text('body')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_notifications');
        Schema::dropIfExists('billing_histories');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('restaurant_applications');
        Schema::dropIfExists('plans');
    }
};
