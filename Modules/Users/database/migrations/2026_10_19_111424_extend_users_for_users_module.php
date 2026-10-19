<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('surname')->default('');
            $table->string('username')->nullable()->unique();
            $table->string('profile_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->ipAddress('last_login_ip')->nullable();
            $table->text('last_login_user_agent')->nullable();
            $table->softDeletes();
        });

        Schema::create('user_logins', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('logged_in_at');
            $table->timestamps();
            $table->index(['user_id', 'logged_in_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_logins');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['username']);
            $table->dropColumn(['surname', 'username', 'profile_image', 'is_active', 'last_login_at', 'last_login_ip', 'last_login_user_agent', 'deleted_at']);
        });
    }
};
