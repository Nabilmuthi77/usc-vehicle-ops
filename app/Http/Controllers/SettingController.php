<?php

namespace App\Http\Controllers;

use App\Enums\FuelType;
use App\Models\FuelPrice;
use App\Models\Setting;
use App\Services\FileUploadService;
use App\Services\SettingService;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

/** FR-M1-08 & FR-M3-19 — pengaturan sistem dan master harga BBM. */
class SettingController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly FileUploadService $uploads,
    ) {}

    /** @return array<int, Middleware> */
    public static function middleware(): array
    {
        return [new Middleware('can:'.Permissions::SETTING_MANAGE)];
    }

    public function index(): View
    {
        return view('settings.index', [
            'settings' => Setting::orderBy('group')->orderBy('key')->get()->groupBy('group'),
            'values' => $this->settings->all(),
            // FR-M3-19 — harga BBM berlaku beserta riwayatnya.
            'fuelPrices' => FuelPrice::query()
                ->orderBy('fuel_type')
                ->orderByDesc('effective_date')
                ->get()
                ->groupBy(fn (FuelPrice $price) => $price->fuel_type->value),
            'fuelTypes' => FuelType::options(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $maxSize = (int) config('usc_vehicle_ops.uploads.max_size_kb', 5120);
        $mimes = 'mimes:'.implode(',', config('usc_vehicle_ops.uploads.image_mimes', ['jpg', 'png']));

        $request->validate([
            'settings' => ['required', 'array'],
            'company_logo' => ['nullable', 'file', $mimes, 'max:'.$maxSize],
        ]);

        // Hanya kunci yang sudah terdaftar di tabel `settings` yang diterima,
        // agar form tidak dapat menyisipkan pengaturan sembarang.
        $known = Setting::pluck('key')->all();

        $this->settings->setMany(
            collect($request->input('settings'))
                ->only($known)
                ->all(),
        );

        if ($request->hasFile('company_logo')) {
            $this->settings->set(
                'company_logo_path',
                $this->uploads->replaceImage(
                    $this->settings->get('company_logo_path'),
                    $request->file('company_logo'),
                    'branding',
                ),
            );
        }

        return redirect()->route('settings.index')->with('status', 'Pengaturan sistem disimpan.');
    }

    /** FR-M3-19 — pencatatan perubahan harga BBM dengan riwayat. */
    public function storeFuelPrice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fuel_type' => ['required', FuelType::rule()],
            'price_per_liter' => ['required', 'numeric', 'gt:0'],
            'effective_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'fuel_type' => 'jenis BBM',
            'price_per_liter' => 'harga per liter',
            'effective_date' => 'tanggal berlaku',
        ]);

        FuelPrice::updateOrCreate(
            [
                'fuel_type' => $validated['fuel_type'],
                'effective_date' => $validated['effective_date'],
            ],
            [
                'price_per_liter' => $validated['price_per_liter'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()->getKey(),
            ],
        );

        return redirect()->route('settings.index')->with('status', 'Harga BBM diperbarui.');
    }
}
