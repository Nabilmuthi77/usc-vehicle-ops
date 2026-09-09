<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Basis seluruh notifikasi USC_VEHICLE_OPS (Modul M7).
 *
 * K-04 — kanal dibatasi pada `database` (in-app) dan `mail`. Kanal efektif
 * ditentukan preferensi pengguna (FR-M7-04); email dikirim lewat queue agar
 * tidak memblokir request (PRD §10.1).
 */
abstract class UscVehicleOpsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** Kunci kejadian untuk preferensi & log notifikasi. */
    abstract public static function eventKey(): string;

    /** Judul singkat yang tampil pada daftar notifikasi in-app. */
    abstract public function title(): string;

    /** Penjelasan detail kejadian. */
    abstract public function detail(): string;

    /** Ikon dan kategori untuk tampilan in-app. */
    public function icon(): string
    {
        return 'bell';
    }

    public function category(): string
    {
        return 'Umum';
    }

    /** Tautan tindak lanjut, bila ada. */
    public function actionUrl(): ?string
    {
        return null;
    }

    /**
     * FR-M7-04 — kanal mengikuti preferensi pengguna.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if ($notifiable instanceof User) {
            return $notifiable->notificationChannelsFor(static::eventKey());
        }

        return ['database'];
    }

    /**
     * Payload in-app (FR-M7-01).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event_key' => static::eventKey(),
            'title' => $this->title(),
            'detail' => $this->detail(),
            'icon' => $this->icon(),
            'category' => $this->category(),
            'action_url' => $this->actionUrl(),
        ];
    }
}
