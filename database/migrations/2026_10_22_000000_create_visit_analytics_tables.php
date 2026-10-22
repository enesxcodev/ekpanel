<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_analytics', function (Blueprint $table): void {
            $table->id();
            $table->date('stat_date');
            $table->string('scope_key', 255);
            $table->string('visitor_type', 16);
            $table->string('device_type', 16);
            $table->unsignedBigInteger('visit_count')->default(0);
            $table->timestamps();
            $table->unique(['stat_date', 'scope_key', 'visitor_type', 'device_type'], 'visit_analytics_daily_unique');
            $table->index(['scope_key', 'stat_date'], 'visit_analytics_scope_date_idx');
            $table->index('stat_date', 'visit_analytics_date_idx');
        });

        Schema::create('visit_analytics_guards', function (Blueprint $table): void {
            $table->id();
            $table->char('identity_hash', 64);
            $table->char('scope_hash', 64);
            $table->timestamp('expires_at');
            $table->unique(['identity_hash', 'scope_hash'], 'visit_analytics_guard_unique');
            $table->index('expires_at', 'visit_analytics_guard_expiry_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_analytics_guards');
        Schema::dropIfExists('visit_analytics');
    }
};
