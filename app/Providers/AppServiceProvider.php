<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // React (Vite) SPA alada e host hoy, tai email er link gulo sheikhane point korano hocche
        // .env e FRONTEND_URL set kore dite hobe (e.g. http://localhost:5173)
        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');

        ResetPassword::createUrlUsing(function ($user, string $token) use ($frontendUrl) {
            return "{$frontendUrl}/admin/reset-password?token={$token}&email=" . urlencode($user->getEmailForPasswordReset());
        });

        VerifyEmail::createUrlUsing(function ($notifiable) {
            $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id'   => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            // signed API URL take frontend page e wrap kore pathano hocche,
            // jate user click korle nice UI dekhe, raw JSON na
            return "{$frontendUrl}/admin/verify-email?verify_url=" . urlencode($signedUrl);
        });
    }
}
