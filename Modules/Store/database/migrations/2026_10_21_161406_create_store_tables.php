<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('extra_fields')->nullable();
            $table->timestamps();
        });

        Schema::create('store_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_category_id')->nullable()->constrained('store_categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->text('short_description')->nullable();
            $table->text('content')->nullable();
            $table->unsignedBigInteger('price_minor');
            $table->unsignedBigInteger('compare_at_price_minor')->nullable();
            $table->char('currency', 3)->default('TRY');
            $table->boolean('tracks_stock')->default(true);
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(0);
            $table->boolean('allow_backorders')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('extra_fields')->nullable();
            $table->timestamps();
        });

        Schema::create('store_variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_product_id')->constrained('store_products')->cascadeOnDelete();
            $table->string('title');
            $table->string('sku')->unique();
            $table->json('option_values')->nullable();
            $table->unsignedBigInteger('price_minor')->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('store_orders', function (Blueprint $table): void {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->json('shipping_address')->nullable();
            $table->char('currency', 3);
            $table->unsignedBigInteger('subtotal_minor');
            $table->unsignedBigInteger('tax_minor')->default(0);
            $table->unsignedBigInteger('shipping_minor')->default(0);
            $table->unsignedBigInteger('discount_minor')->default(0);
            $table->unsignedBigInteger('total_minor');
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->timestamp('stock_released_at')->nullable();
            $table->json('status_history')->nullable();
            $table->timestamps();
        });

        Schema::create('store_order_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_order_id')->constrained('store_orders')->cascadeOnDelete();
            $table->unsignedBigInteger('store_product_id')->nullable();
            $table->unsignedBigInteger('store_variant_id')->nullable();
            $table->string('product_title');
            $table->string('variant_title')->nullable();
            $table->string('sku');
            $table->unsignedBigInteger('unit_price_minor');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_total_minor');
            $table->json('snapshot')->nullable();
            $table->timestamps();
        });

        Schema::create('store_stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_product_id')->constrained('store_products')->cascadeOnDelete();
            $table->foreignId('store_variant_id')->nullable()->constrained('store_variants')->cascadeOnDelete();
            $table->integer('quantity');
            $table->integer('resulting_quantity');
            $table->string('reason');
            $table->nullableMorphs('reference');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_stock_movements');
        Schema::dropIfExists('store_order_lines');
        Schema::dropIfExists('store_orders');
        Schema::dropIfExists('store_variants');
        Schema::dropIfExists('store_products');
        Schema::dropIfExists('store_categories');
    }
};
