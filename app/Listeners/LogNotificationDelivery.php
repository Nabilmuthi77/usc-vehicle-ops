<?php

namespace App\Listeners;

use App\Models\NotificationLog;
use App\Models\User;
use App\Notifications\UscVehicleOpsNotification;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Carbon;

/**
 * FR-M7-05 — riwayat pengiriman notifikasi beserta status berhasil/gagal,
 * agar Admin dapat menelusuri email yang tidak sampai.
 */
class LogNotificationDelivery
{
    public function handleSent(NotificationSent $event): void
    {
        $this->write($event->notifiable, $event->notification, $event->channel, 'terkirim');
    }

    public function handleFailed(NotificationFailed $event): void
    {
        $this->write(
            $event->notifiable,
            $event->notification,
            $event->channel,
            'gagal',
            $event->data['message'] ?? json_encode($event->data),
        );
    }

    private function write(
        mixed $notifiable,
        mixed $notification,
        string $channel,
        string $status,
        ?string $error = null,
    ): void {
        if (! $notification instanceof UscVehicleOpsNotification) {
            return;
        }

        $attributes = [
            'user_id' => $notifiable instanceof User ? $notifiable->getKey() : null,
            'event_key' => $notification::eventKey(),
            'channel' => $channel,
            'notification_class' => $notification::class,
            'recipient' => $notifiable instanceof User ? $notifiable->email : null,
            'subject' => $notification->title(),
            'status' => $status,
            'error_message' => $error,
            'sent_at' => Carbon::now(),
        ];

        // Satu pengiriman = satu baris. Event NotificationSent dapat terpicu
        // lebih dari sekali untuk notifikasi ber-queue, sehingga penulisan
        // dikunci pada kombinasi UUID notifikasi + kanal.
        if (filled($notification->id)) {
            NotificationLog::updateOrCreate(
                ['notification_id' => $notification->id, 'channel' => $channel],
                $attributes,
            );

            return;
        }

        NotificationLog::create($attributes);
    }
}
