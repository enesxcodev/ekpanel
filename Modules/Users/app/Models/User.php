<?php

namespace Modules\Users\Models;

use App\Models\User as BaseUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Database\Factories\UserFactory;
use Modules\Users\Notifications\ResetPassword;
use Modules\Users\Notifications\VerifyEmail;

#[Fillable(['name', 'surname', 'email', 'password', 'username', 'profile_image', 'is_active'])]
class User extends BaseUser implements MustVerifyEmail
{
    use SoftDeletes;

    protected $table = 'users';

    protected function casts(): array
    {
        return [...parent::casts(), 'is_active' => 'boolean', 'last_login_at' => 'datetime'];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function logins(): HasMany
    {
        return $this->hasMany(UserLogin::class)->orderByDesc('logged_in_at')->orderByDesc('id');
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail);
    }

    public function sendPasswordResetNotification(mixed $token): void
    {
        $this->notify(new ResetPassword($token));
    }

    protected static function booted(): void
    {
        static::updating(function (User $user): void {
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
        });
    }
}
