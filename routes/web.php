<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\FuelController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReimbursementController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TollController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VendorController;
use App\Support\Permissions;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/search', [\App\Http\Controllers\SearchController::class, 'index'])->name('search');

    /*
    |----------------------------------------------------------------------
    | — Peminjaman Kendaraan
    |----------------------------------------------------------------------
    */
    Route::prefix('peminjaman')->name('bookings.')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::get('/penugasan-driver', [\App\Http\Controllers\DriverAssignmentController::class, 'index'])->name('driver-assignments.index');
        Route::get('/antrean-approval', [BookingController::class, 'approvalQueue'])
            ->middleware('can:'.Permissions::BOOKING_APPROVE)
            ->name('approval-queue');
        // — papan penugasan harian.
        Route::get('/papan-tugas', [BookingController::class, 'assignmentBoard'])->name('assignment-board');
        Route::get('/ajukan', [BookingController::class, 'create'])->name('create');
        Route::post('/', [BookingController::class, 'store'])->name('store');
        Route::get('/{booking}', [BookingController::class, 'show'])->name('show');
        Route::post('/{booking}/setujui', [BookingController::class, 'approve'])->name('approve');
        Route::post('/{booking}/tolak', [BookingController::class, 'reject'])->name('reject');
        Route::post('/{booking}/batalkan', [BookingController::class, 'cancel'])->name('cancel');
        Route::post('/{booking}/serah-terima', [BookingController::class, 'checkOut'])->name('check-out');
        Route::post('/{booking}/pengembalian', [BookingController::class, 'checkIn'])->name('check-in');
        // — cetak surat jalan.
        Route::get('/{booking}/cetak', [BookingController::class, 'printHandover'])->name('print');
    });

    /*
    |----------------------------------------------------------------------
    | — Pemakaian BBM & Reimbursement
    |----------------------------------------------------------------------
    */
    Route::prefix('bbm')->name('fuel.')->group(function () {
        Route::get('/', [FuelController::class, 'index'])->name('index');
        // — halaman "Klaim Saya".
        Route::get('/klaim-saya', [FuelController::class, 'myClaims'])->name('my-claims');
        Route::get('/tambah', [FuelController::class, 'create'])->name('create');
        Route::post('/', [FuelController::class, 'store'])->name('store');
        Route::get('/{fuel}', [FuelController::class, 'show'])->name('show');
        Route::get('/{fuel}/ubah', [FuelController::class, 'edit'])->name('edit');
        Route::put('/{fuel}', [FuelController::class, 'update'])->name('update');
        Route::post('/{fuel}/ajukan', [FuelController::class, 'submitClaim'])->name('submit-claim');
        Route::post('/{fuel}/verifikasi', [FuelController::class, 'verify'])->name('verify');
        Route::post('/{fuel}/tolak', [FuelController::class, 'reject'])->name('reject');
        Route::delete('/{fuel}', [FuelController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('reimbursement')->name('reimbursements.')->group(function () {
        Route::get('/', [ReimbursementController::class, 'index'])->name('index');
        Route::get('/susun', [ReimbursementController::class, 'create'])->name('create');
        Route::post('/', [ReimbursementController::class, 'store'])->name('store');
        Route::get('/{reimbursement}', [ReimbursementController::class, 'show'])->name('show');
        // — rekap PDF untuk Finance.
        Route::get('/{reimbursement}/cetak', [ReimbursementController::class, 'print'])->name('print');
        Route::post('/{reimbursement}/serahkan', [ReimbursementController::class, 'submitToFinance'])
            ->name('submit');
        // — penandaan pembayaran.
        Route::post('/{reimbursement}/bayar', [ReimbursementController::class, 'markAsPaid'])->name('pay');
        Route::delete('/{reimbursement}/klaim/{claim}', [ReimbursementController::class, 'removeClaim'])
            ->name('remove-claim');
    });

    /*
    |----------------------------------------------------------------------
    | — Servis Berkala & Permintaan Servis Vendor
    |----------------------------------------------------------------------
    */
    Route::prefix('servis')->name('service.')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('index');
        Route::get('/tambah', [ServiceController::class, 'create'])->name('create');
        Route::post('/', [ServiceController::class, 'store'])->name('store');
        Route::get('/riwayat/{vehicle}', [ServiceController::class, 'history'])->name('history');
        Route::get('/jadwal/{schedule}/ubah', [ServiceController::class, 'editSchedule'])->name('edit-schedule');
        Route::put('/jadwal/{schedule}', [ServiceController::class, 'updateSchedule'])->name('update-schedule');
        Route::get('/{serviceRecord}', [ServiceController::class, 'show'])->name('show');
    });

    Route::prefix('permintaan-servis')->name('service-requests.')->group(function () {
        Route::get('/', [ServiceRequestController::class, 'index'])->name('index');
        Route::get('/buat', [ServiceRequestController::class, 'create'])->name('create');
        Route::post('/', [ServiceRequestController::class, 'store'])->name('store');
        Route::get('/{serviceRequest}', [ServiceRequestController::class, 'show'])->name('show');
        // — kirim ke vendor & cetak PDF.
        Route::post('/{serviceRequest}/kirim', [ServiceRequestController::class, 'send'])->name('send');
        Route::get('/{serviceRequest}/cetak', [ServiceRequestController::class, 'print'])->name('print');
        Route::post('/{serviceRequest}/status', [ServiceRequestController::class, 'updateStatus'])
            ->name('update-status');
        Route::post('/{serviceRequest}/selesai', [ServiceRequestController::class, 'complete'])->name('complete');
    });

    /*
    |----------------------------------------------------------------------
    | — Biaya Tol
    |----------------------------------------------------------------------
    */
    Route::prefix('tol')->name('toll.')->group(function () {
        Route::get('/', [TollController::class, 'index'])->name('index');
        Route::post('/transaksi', [TollController::class, 'storeTransaction'])->name('transactions.store');
        Route::delete('/transaksi/{tollTransaction}', [TollController::class, 'destroyTransaction'])
            ->name('transactions.destroy');
        Route::post('/topup', [TollController::class, 'storeTopup'])->name('topup');
        // — rekonsiliasi saldo.
        Route::post('/rekonsiliasi', [TollController::class, 'reconcile'])->name('reconcile');
        // — pencarian tarif otomatis.
        Route::get('/tarif', [TollController::class, 'lookupRate'])->name('rate-lookup');
        Route::post('/kartu', [TollController::class, 'storeCard'])->name('cards.store');
        Route::put('/kartu/{card}', [TollController::class, 'updateCard'])->name('cards.update');
    });

    /*
    |----------------------------------------------------------------------
    | — Master Data
    |----------------------------------------------------------------------
    */
    Route::prefix('kendaraan')->name('vehicles.')->group(function () {
        Route::get('/', [VehicleController::class, 'index'])->name('index');
        Route::get('/export', [VehicleController::class, 'export'])->name('export');
        Route::get('/tambah', [VehicleController::class, 'create'])->name('create');
        Route::post('/', [VehicleController::class, 'store'])->name('store');
        Route::get('/{vehicle}', [VehicleController::class, 'show'])->name('show');
        Route::get('/{vehicle}/ubah', [VehicleController::class, 'edit'])->name('edit');
        Route::put('/{vehicle}', [VehicleController::class, 'update'])->name('update');
        Route::delete('/{vehicle}', [VehicleController::class, 'destroy'])->name('destroy');
        // — koreksi odometer oleh Admin.
        Route::post('/{vehicle}/koreksi-odometer', [VehicleController::class, 'correctOdometer'])
            ->name('correct-odometer');
            
        // — dokumen kendaraan.
        Route::get('/{vehicle}/dokumen/tambah', [\App\Http\Controllers\VehicleDocumentController::class, 'create'])->name('documents.create');
        Route::post('/{vehicle}/dokumen', [\App\Http\Controllers\VehicleDocumentController::class, 'store'])->name('documents.store');
        Route::get('/{vehicle}/dokumen/{document}/ubah', [\App\Http\Controllers\VehicleDocumentController::class, 'edit'])->name('documents.edit');
        Route::put('/{vehicle}/dokumen/{document}', [\App\Http\Controllers\VehicleDocumentController::class, 'update'])->name('documents.update');
        Route::delete('/{vehicle}/dokumen/{document}', [\App\Http\Controllers\VehicleDocumentController::class, 'destroy'])->name('documents.destroy');
    });

    Route::prefix('master-data')->group(function () {
        Route::resource('driver', DriverController::class)
            ->parameters(['driver' => 'driver'])
            ->names('drivers');

        Route::resource('departemen', DepartmentController::class)
            ->parameters(['departemen' => 'department'])
            ->except('show')
            ->names('departments');

        Route::resource('vendor', VendorController::class)
            ->parameters(['vendor' => 'vendor'])
            ->names('vendors');


        // — kontrak sewa.
        Route::get('kontrak-sewa/buat', [VendorController::class, 'createContract'])
            ->name('rental-contracts.create');
        Route::post('kontrak-sewa', [VendorController::class, 'storeContract'])
            ->name('rental-contracts.store');
        Route::get('kontrak-sewa/{rentalContract}', [VendorController::class, 'showContract'])
            ->name('rental-contracts.show');
    });

    /*
    |----------------------------------------------------------------------
    | — Laporan & Export
    |----------------------------------------------------------------------
    */
    Route::prefix('laporan')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export/excel', [ReportController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [ReportController::class, 'exportPdf'])->name('export.pdf');
    });

    /*
    |----------------------------------------------------------------------
    | Administrasi
    |----------------------------------------------------------------------
    */
    Route::resource('pengguna', UserController::class)
        ->parameters(['pengguna' => 'user'])
        ->except('show')
        ->names('users');

    // — audit log.
    Route::get('/audit-log', [UserController::class, 'auditLog'])->name('audit-log');

    Route::get('/pengaturan', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/pengaturan', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/pengaturan/harga-bbm', [SettingController::class, 'storeFuelPrice'])
        ->name('settings.fuel-price');

    /*
    |----------------------------------------------------------------------
    | — Notifikasi
    |----------------------------------------------------------------------
    */
    Route::prefix('notifikasi')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/{notification}/klik', [NotificationController::class, 'click'])->name('click');
        Route::post('/{notification}/dibaca', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/dibaca-semua', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/hapus-massal', [NotificationController::class, 'bulkDestroy'])->name('bulk-destroy');
        // — preferensi kanal per pengguna.
        Route::get('/preferensi', [NotificationController::class, 'preferences'])->name('preferences');
        Route::put('/preferensi', [NotificationController::class, 'updatePreferences'])
            ->name('preferences.update');
        // — log pengiriman notifikasi.
        Route::get('/log', [NotificationController::class, 'logs'])->name('logs');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
