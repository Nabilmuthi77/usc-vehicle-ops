<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\FuelTransaction;
use App\Models\ServiceRequest;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');

        if (blank($q) || strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];
        $user = $request->user();
        $seesOnlyOwn = $user->seesOnlyOwnRecords();
        $isAdmin = $user->hasAnyRole(['Admin Sistem', 'Admin GA']);

        // 1. Peminjaman
        $bookings = Booking::query()
            ->when($seesOnlyOwn, fn($query) => $query->where('requester_id', $user->id))
            ->where(function ($query) use ($q) {
                $query->where('booking_number', 'like', "%{$q}%")
                      ->orWhere('destination', 'like', "%{$q}%");
            })
            ->take(3)
            ->get();
            
        foreach ($bookings as $b) {
            $results[] = [
                'type' => 'Peminjaman',
                'title' => $b->booking_number,
                'subtitle' => $b->destination,
                'url' => route('bookings.show', $b),
                'icon' => 'calendar',
            ];
        }

        // 2. BBM
        $fuels = FuelTransaction::query()
            ->when($seesOnlyOwn, fn($query) => $query->where('claimant_id', $user->id))
            ->where('receipt_number', 'like', "%{$q}%")
            ->take(3)
            ->get();
            
        foreach ($fuels as $f) {
            $results[] = [
                'type' => 'BBM',
                'title' => $f->receipt_number,
                'subtitle' => $f->vehicle?->plate_number ?? 'Nota BBM',
                'url' => route('fuel.show', $f),
                'icon' => 'droplet',
            ];
        }

        // 3. Kendaraan (Hanya Admin)
        if ($isAdmin) {
            $vehicles = Vehicle::search($q)
                ->take(3)
                ->get();
            foreach ($vehicles as $v) {
                $results[] = [
                    'type' => 'Kendaraan',
                    'title' => $v->plate_number,
                    'subtitle' => $v->full_name,
                    'url' => route('vehicles.show', $v),
                    'icon' => 'truck',
                ];
            }
        }

        // 4. Pengajuan Servis (Hanya Admin)
        if ($isAdmin) {
            $services = ServiceRequest::where('request_number', 'like', "%{$q}%")
                ->take(3)
                ->get();
            foreach ($services as $s) {
                $results[] = [
                    'type' => 'Pengajuan Servis',
                    'title' => $s->request_number,
                    'subtitle' => $s->vehicle?->plate_number ?? 'Pengajuan Servis',
                    'url' => route('service-requests.show', $s),
                    'icon' => 'wrench',
                ];
            }
        }

        // 5. Riwayat Servis (Hanya Admin)
        if ($isAdmin) {
            $records = \App\Models\ServiceRecord::where('invoice_number', 'like', "%{$q}%")
                ->take(3)
                ->get();
            foreach ($records as $r) {
                $results[] = [
                    'type' => 'Riwayat Servis',
                    'title' => $r->invoice_number ?? 'Servis Tanpa Nota',
                    'subtitle' => $r->vehicle?->plate_number ?? 'Riwayat Servis',
                    'url' => route('service.show', $r),
                    'icon' => 'wrench',
                ];
            }
        }

        return response()->json(['results' => $results]);
    }
}
