<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\NotificationPreference;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Modul M7 — notifikasi in-app, preferensi, dan log pengiriman. */
class NotificationController extends Controller
{
    /** FR-M7-01 — daftar notifikasi in-app dengan status dibaca/belum. */
    public function index(Request $request): View
    {
        $query = $request->user()->notifications();

        if ($request->boolean('unread')) {
            $query = $request->user()->unreadNotifications();
        }

        return view('notifications.index', [
            'notifications' => $query->paginate(10)->withQueryString(),
            'unreadCount' => $request->user()->unreadNotifications()->count(),
            'filters' => $request->only('unread'),
        ]);
    }

    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($notification)->markAsRead();

        return back();
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'Semua notifikasi ditandai telah dibaca.');
    }

    public function destroy(Request $request, string $notification): RedirectResponse
    {
        $notif = $request->user()->notifications()->findOrFail($notification);
        $this->assertCanBeDeleted($notif);
        $notif->delete();
        
        return back()->with('status', 'Notifikasi berhasil dihapus.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'notifications' => ['required', 'array'],
            'notifications.*' => ['string'],
        ]);

        $notifications = $request->user()->notifications()->whereIn('id', $validated['notifications'])->get();
        foreach ($notifications as $notif) {
            $this->assertCanBeDeleted($notif);
            $notif->delete();
        }

        return back()->with('status', count($notifications) . ' notifikasi berhasil dihapus.');
    }

    private function assertCanBeDeleted($notification): void
    {
        if ($notification->type === \App\Notifications\BookingSubmitted::class) {
            $url = $notification->data['action_url'] ?? '';
            if (preg_match('/\/peminjaman\/(\d+)/', $url, $matches)) {
                $booking = \App\Models\Booking::find($matches[1]);
                if ($booking && $booking->status === \App\Enums\BookingStatus::MenungguApproval) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'notification' => 'Notifikasi pengajuan yang belum diproses tidak dapat dihapus.'
                    ]);
                }
            }
        }
    }

    /** FR-M7-04 — pengaturan preferensi kanal per jenis kejadian. */
    public function preferences(Request $request): View
    {
        $preferences = $request->user()
            ->notificationPreferences()
            ->get()
            ->keyBy('event_key');

        return view('notifications.preferences', [
            'events' => $this->availableEvents(),
            'preferences' => $preferences,
        ]);
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preferences' => ['required', 'array'],
            'preferences.*.via_database' => ['nullable', 'boolean'],
            'preferences.*.via_mail' => ['nullable', 'boolean'],
        ]);

        foreach ($validated['preferences'] as $eventKey => $channels) {
            if (! array_key_exists($eventKey, $this->availableEvents())) {
                continue;
            }

            NotificationPreference::updateOrCreate(
                ['user_id' => $request->user()->getKey(), 'event_key' => $eventKey],
                [
                    'via_database' => (bool) ($channels['via_database'] ?? false),
                    'via_mail' => (bool) ($channels['via_mail'] ?? false),
                ],
            );
        }

        return back()->with('status', 'Preferensi notifikasi disimpan.');
    }

    /** FR-M7-05 — riwayat pengiriman notifikasi untuk Admin. */
    public function logs(Request $request): View
    {
        $this->authorize('viewAny', \App\Models\User::class);

        abort_unless($request->user()->can(Permissions::AUDIT_LOG_VIEW), 403);

        return view('notifications.logs', [
            'logs' => NotificationLog::query()
                ->with('user')
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->when($request->filled('event_key'), fn ($q) => $q->where('event_key', $request->string('event_key')))
                ->orderByDesc('sent_at')
                ->paginate(50)
                ->withQueryString(),
            'events' => $this->availableEvents(),
            'filters' => $request->only(['status', 'event_key']),
        ]);
    }

    public function click(Request $request, string $notification): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($notification);
        $notification->markAsRead();
        
        $url = $notification->data['action_url'] ?? route('notifications.index');
        return redirect($url);
    }

    /**
     * Jenis kejadian yang dapat diatur preferensinya (FR-M7-02).
     *
     * @return array<string, string>
     */
    private function availableEvents(): array
    {
        return [
            'booking_submitted' => 'Pengajuan peminjaman baru',
            'booking_cancelled' => 'Pengajuan peminjaman dibatalkan',
            'booking_approved' => 'Peminjaman disetujui',
            'booking_rejected' => 'Peminjaman ditolak',
            'driver_assigned' => 'Penugasan driver',
            'booking_overdue' => 'Peminjaman terlambat dikembalikan',
            'fuel_claim_submitted' => 'Klaim BBM diajukan',
            'fuel_claim_verified' => 'Klaim BBM terverifikasi',
            'fuel_claim_rejected' => 'Klaim BBM ditolak',
            'reimbursement_paid' => 'Reimbursement dibayar',
            'service_due' => 'Servis segera / jatuh tempo',
            'service_request_escalation' => 'Eskalasi permintaan servis vendor',
            'document_expiring' => 'Dokumen kendaraan akan kedaluwarsa',
            'license_expiring' => 'SIM driver akan kedaluwarsa',
            'toll_balance_low' => 'Saldo e-toll dibawah minimum saldo',
            'rental_contract_expiring' => 'Kontrak sewa akan berakhir',
            'pending_claim_reminder' => 'Pengingat klaim menunggu verifikasi',
            'daily_digest' => 'Ringkasan harian',
        ];
    }
}
