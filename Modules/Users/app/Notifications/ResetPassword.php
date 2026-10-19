<?php

namespace Modules\Users\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;

class ResetPassword extends BaseResetPassword
{
    protected function resetUrl(mixed $notifiable): string
    {
        return route('users.password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()]);
    }
}
