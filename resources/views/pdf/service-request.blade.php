{{-- FR-M4-17 — cetak/export PDF permintaan servis ke vendor. --}}
@extends('pdf.layout')

@section('title', 'Permintaan Servis Kendaraan')

@section('doc-number')
    <strong>{{ $request->request_number }}</strong><br>
    <span class="muted">{{ $request->created_at?->format('d-m-Y') }}</span>
@endsection

@section('content')
    <h2>Kepada Vendor</h2>
    <table class="meta">
        <tr><td class="label">Nama vendor</td><td>{{ $request->vendor?->name }}</td></tr>
        <tr><td class="label">PIC</td><td>{{ $request->vendor?->pic_name ?: '-' }}</td></tr>
        <tr><td class="label">Telepon</td><td>{{ $request->vendor?->pic_phone ?: '-' }}</td></tr>
        <tr><td class="label">Email</td><td>{{ $request->sent_to_email ?: ($request->vendor?->pic_email ?: '-') }}</td></tr>
    </table>

    <h2>Data Kendaraan</h2>
    <table class="meta">
        <tr><td class="label">Nomor polisi</td><td>{{ $request->vehicle?->plate_number }}</td></tr>
        <tr><td class="label">Merk / tipe</td><td>{{ $request->vehicle?->full_name }}</td></tr>
        <tr><td class="label">Tahun</td><td>{{ $request->vehicle?->year }}</td></tr>
        <tr><td class="label">Kepemilikan</td><td>{{ $request->vehicle?->ownership->label() }}</td></tr>
        <tr><td class="label">Odometer saat ini</td><td>{{ number_format((int) $request->current_odometer, 0, ',', '.') }} km</td></tr>
    </table>

    <h2>Rincian Permintaan</h2>
    <table class="meta">
        <tr><td class="label">Jenis servis</td><td>{{ $request->serviceType?->name ?? 'Pemeriksaan umum' }}</td></tr>
        <tr><td class="label">KM jatuh tempo</td>
            <td>{{ $request->due_odometer ? number_format((int) $request->due_odometer, 0, ',', '.').' km' : '-' }}</td></tr>
        <tr><td class="label">Status</td><td>{{ $request->status->label() }}</td></tr>
        <tr><td class="label">Catatan keluhan</td><td>{{ $request->complaint_note ?: '-' }}</td></tr>
    </table>

    <p class="muted">
        Biaya servis kendaraan sewa/leasing ditanggung vendor sesuai kontrak yang berlaku.
    </p>

    <table class="signatures">
        <tr>
            <td>Admin GA</td>
            <td></td>
            <td>Diterima Vendor</td>
        </tr>
        <tr>
            <td class="sign-space"></td>
            <td></td>
            <td class="sign-space"></td>
        </tr>
        <tr>
            <td class="sign-line">{{ $request->createdBy?->name ?? '(..............................)' }}</td>
            <td></td>
            <td class="sign-line">(..............................)</td>
        </tr>
    </table>
@endsection
