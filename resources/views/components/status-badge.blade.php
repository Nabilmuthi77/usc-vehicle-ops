@props(['status'])

@php
    // Menerima string maupun enum status (App\Enums\*) dari model Eloquent.
    $status = $status instanceof \BackedEnum ? $status->value : (string) $status;

    $map = [
        // Kendaraan
        'tersedia' => ['Tersedia', 'bg-green-50 text-green-700 ring-green-600/20'],
        'dipinjam' => ['Dipinjam', 'bg-blue-50 text-blue-700 ring-blue-600/20'],
        'servis' => ['Servis', 'bg-amber-50 text-amber-700 ring-amber-600/20'],
        'nonaktif' => ['Nonaktif', 'bg-gray-100 text-gray-600 ring-gray-500/20'],

        // Peminjaman
        'draft' => ['Draft', 'bg-gray-100 text-gray-600 ring-gray-500/20'],
        'menunggu_approval' => ['Menunggu Approval', 'bg-amber-50 text-amber-700 ring-amber-600/20'],
        'disetujui' => ['Disetujui', 'bg-blue-50 text-blue-700 ring-blue-600/20'],
        'ditolak' => ['Ditolak', 'bg-red-50 text-red-700 ring-red-600/20'],
        'sedang_digunakan' => ['Sedang Digunakan', 'bg-violet-50 text-violet-700 ring-violet-600/20'],
        'selesai' => ['Selesai', 'bg-green-50 text-green-700 ring-green-600/20'],
        'dibatalkan' => ['Dibatalkan', 'bg-gray-100 text-gray-500 ring-gray-500/20'],

        // Klaim BBM & batch
        'diajukan' => ['Diajukan', 'bg-amber-50 text-amber-700 ring-amber-600/20'],
        'terverifikasi' => ['Terverifikasi', 'bg-blue-50 text-blue-700 ring-blue-600/20'],
        'dibayar' => ['Dibayar', 'bg-green-50 text-green-700 ring-green-600/20'],
        'disusun' => ['Disusun', 'bg-gray-100 text-gray-600 ring-gray-500/20'],
        'diserahkan_finance' => ['Diserahkan Finance', 'bg-blue-50 text-blue-700 ring-blue-600/20'],

        // Servis (jadwal)
        'aman' => ['Aman', 'bg-green-50 text-green-700 ring-green-600/20'],
        'segera' => ['Segera Servis', 'bg-amber-50 text-amber-700 ring-amber-600/20'],
        'jatuh_tempo' => ['Jatuh Tempo', 'bg-red-50 text-red-700 ring-red-600/20'],

        // Permintaan servis vendor
        'dibuat' => ['Dibuat', 'bg-gray-100 text-gray-600 ring-gray-500/20'],
        'dikirim' => ['Dikirim ke Vendor', 'bg-blue-50 text-blue-700 ring-blue-600/20'],
        'dijadwalkan' => ['Dijadwalkan', 'bg-indigo-50 text-indigo-700 ring-indigo-600/20'],
        'dikerjakan' => ['Sedang Dikerjakan', 'bg-amber-50 text-amber-700 ring-amber-600/20'],

        // Kartu e-toll
        'aktif' => ['Aktif', 'bg-green-50 text-green-700 ring-green-600/20'],
        'hilang' => ['Hilang', 'bg-red-50 text-red-700 ring-red-600/20'],
    ];

    [$label, $classes] = $map[$status] ?? [ucfirst(str_replace('_', ' ', $status)), 'bg-gray-100 text-gray-600 ring-gray-500/20'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset whitespace-nowrap $classes"]) }}>
    {{ $label }}
</span>
