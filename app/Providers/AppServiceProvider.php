<?php

namespace App\Providers;

use App\Listeners\LogNotificationDelivery;
use App\Models\Booking;
use App\Models\FuelTransaction;
use App\Models\ReimbursementBatch;
use App\Models\ServiceRecord;
use App\Models\ServiceRequest;
use App\Models\TollTransaction;
use App\Models\User;
use App\Models\Vehicle;
use App\Policies\BookingPolicy;
use App\Policies\FuelTransactionPolicy;
use App\Policies\ReimbursementBatchPolicy;
use App\Policies\ServiceRecordPolicy;
use App\Policies\ServiceRequestPolicy;
use App\Policies\TollTransactionPolicy;
use App\Policies\UserPolicy;
use App\Policies\VehiclePolicy;
use App\Services\SettingService;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /** PRD §10.2 — pemetaan model ke policy. */
    private const POLICIES = [
        Booking::class => BookingPolicy::class,
        FuelTransaction::class => FuelTransactionPolicy::class,
        ReimbursementBatch::class => ReimbursementBatchPolicy::class,
        ServiceRecord::class => ServiceRecordPolicy::class,
        ServiceRequest::class => ServiceRequestPolicy::class,
        TollTransaction::class => TollTransactionPolicy::class,
        User::class => UserPolicy::class,
        Vehicle::class => VehiclePolicy::class,
    ];

    public function register(): void
    {
        $this->app->singleton(SettingService::class);
    }

    public function boot(): void
    {
        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers());

        \Illuminate\Auth\Notifications\ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Atur Ulang Kata Sandi - USC Vehicle Ops')
                ->view('emails.reset-password', [
                    'url' => url(route('password.reset', [
                        'token' => $token,
                        'email' => $notifiable->getEmailForPasswordReset(),
                    ], false)),
                    'user' => $notifiable
                ]);
        });

        foreach (self::POLICIES as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // NFR Keamanan — cegah pengguna nonaktif tetap dapat mengakses sistem.
        Gate::before(function (User $user) {
            return $user->is_active ? null : false;
        });

        // FR-M7-05 — catat seluruh pengiriman notifikasi.
        Event::listen(NotificationSent::class, [LogNotificationDelivery::class, 'handleSent']);
        Event::listen(NotificationFailed::class, [LogNotificationDelivery::class, 'handleFailed']);

        // Update last_login_at saat user login
        Event::listen(\Illuminate\Auth\Events\Login::class, function (\Illuminate\Auth\Events\Login $event) {
            $event->user->forceFill(['last_login_at' => now()])->save();
        });
    }
}
