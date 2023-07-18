<?php

namespace Iotronlab\FilamentMultiGuard\Concerns;

use Iotronlab\FilamentMultiGuard\Support\MultiGuardResetPasswordNotification;

trait hasMultiguardResetPassword
{

    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new MultiGuardResetPasswordNotification($token));
    }


}
