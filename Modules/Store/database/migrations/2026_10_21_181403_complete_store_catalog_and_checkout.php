<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('store_products', function (Blueprint $table): void {
            $table->string('image')->nullable();
            $table->foreignId('image_media_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->string('share_image')->nullable();
            $table->foreignId('share_image_media_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('has_variants')->default(false);
            $table->json('option_groups')->nullable();
            $table->unsignedBigInteger('source_product_id')->nullable()->index();
            $table->unsignedInteger('weight_grams')->nullable();
            $table->unsignedInteger('width_cm')->nullable();
            $table->unsignedInteger('height_cm')->nullable();
            $table->unsignedInteger('length_cm')->nullable();
            $table->unsignedInteger('preparation_days')->default(0);
            $table->text('delivery_description')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->integer('stock_quantity')->default(0)->change();
        });
        Schema::create('store_product_images', function (Blueprint $table): void {
            $table->foreignId('store_product_id')->constrained('store_products')->cascadeOnDelete();
            $table->foreignId('media_file_id')->constrained('media_files')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['store_product_id', 'media_file_id']);
        });
        Schema::table('store_variants', function (Blueprint $table): void {
            $table->foreignId('image_media_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->boolean('is_archived')->default(false);
            $table->integer('stock_quantity')->default(0)->change();
        });
        Schema::table('store_orders', function (Blueprint $table): void {
            $table->string('checkout_key')->nullable()->unique();
            $table->string('checkout_hash')->nullable();
            $table->string('access_token', 64)->nullable()->unique();
            $table->string('customer_phone')->nullable();
            $table->json('billing_address')->nullable();
            $table->json('delivery_snapshot')->nullable();
            $table->timestamp('reserved_until')->nullable()->index();
            $table->string('tracking_number')->nullable();
            $table->timestamp('shipped_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_orders', function (Blueprint $table): void {
            $table->dropColumn(['checkout_key', 'checkout_hash', 'access_token', 'customer_phone', 'billing_address', 'delivery_snapshot', 'reserved_until', 'tracking_number', 'shipped_at']);
        });
        Schema::table('store_variants', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('image_media_id');
            $table->dropColumn('is_archived');
        });
        Schema::dropIfExists('store_product_images');
        Schema::table('store_products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('image_media_id');
            $table->dropConstrainedForeignId('share_image_media_id');
            $table->dropColumn(['image', 'share_image', 'meta_title', 'meta_description', 'has_variants', 'option_groups', 'source_product_id', 'weight_grams', 'width_cm', 'height_cm', 'length_cm', 'preparation_days', 'delivery_description', 'is_archived']);
        });
    }
};
