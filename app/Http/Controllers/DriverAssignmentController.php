<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriverAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user->hasAnyRole(['Driver', 'Admin GA', 'Admin Sistem', 'Viewer']), 403);

        $bookings = Booking::query()
            ->with(['requester', 'vehicle', 'driver', 'department'])
            ->whereNotNull('driver_id');

        // Jika user adalah Driver dan bukan Admin/Viewer, maka batasi hanya data dirinya sendiri
        if ($user->hasRole('Driver') && !$user->hasAnyRole(['Admin GA', 'Admin Sistem', 'Viewer'])) {
            $driver = Driver::where('user_id', $user->id)->first();
            if ($driver) {
                $bookings->where('driver_id', $driver->id);
            } else {
                // Jika driver tidak ditemukan untuk user ini, return kosong
                $bookings->where('driver_id', 0); 
            }
        }

        $assignments = $bookings
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search')->toString();
                $q->where(function ($query) use ($term) {
                    $query->where('booking_number', 'like', "%{$term}%")
                        ->orWhere('booking_date', 'like', "%{$term}%")
                        ->orWhere('destination', 'like', "%{$term}%")
                        ->orWhere('purpose', 'like', "%{$term}%")
                        ->orWhere('status', 'like', "%{$term}%")
                        ->orWhereHas('requester', fn ($q) => $q->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('vehicle', fn ($q) => $q->where('plate_number', 'like', "%{$term}%"))
                        ->orWhereHas('driver', fn ($q) => $q->where('name', 'like', "%{$term}%"));
                        
                    if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $term, $m)) {
                        $reversed = $m[3] . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
                        $query->orWhere('booking_date', 'like', "%{$reversed}%");
                    }
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('driver-assignments.index', [
            'assignments' => $assignments,
        ]);
    }
}
