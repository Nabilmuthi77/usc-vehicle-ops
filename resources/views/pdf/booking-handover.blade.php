{{-- FR-M2-26 — cetak surat jalan / form serah terima kendaraan. --}}
@extends('pdf.layout')

@section('title', 'Surat Jalan & Form Serah Terima Kendaraan')

@section('doc-number')
    <strong>{{ $booking->booking_number }}</strong><br>
    <span class="muted">{{ $booking->booking_date->format('d-m-Y') }}</span>
@endsection

@section('content')
    <h2>Data Peminjaman</h2>
    <table class="meta">
        <tr><td class="label">Pemohon</td><td>{{ $booking->requester?->name }}</td></tr>
        <tr><td class="label">Departemen</td><td>{{ $booking->department?->name ?: '-' }}</td></tr>
        <tr><td class="label">Tanggal</td><td>{{ $booking->booking_date->format('d-m-Y') }}</td></tr>
        <tr><td class="label">Tujuan</td><td>{{ $booking->destination }}</td></tr>
        <tr><td class="label">Keperluan</td><td>{{ $booking->purpose }}</td></tr>
        <tr><td class="label">Durasi</td><td>{{ $booking->duration_type->label() }}</td></tr>
        <tr><td class="label">Ganjil–genap</td><td>{{ $booking->odd_even_zone ? 'Ya' : 'Tidak' }}</td></tr>
    </table>

    <h2>Kendaraan & Pengemudi</h2>
    <table class="meta">
        <tr><td class="label">Nomor polisi</td><td>{{ $booking->vehicle?->plate_number }}</td></tr>
        <tr><td class="label">Merk / tipe</td><td>{{ $booking->vehicle?->full_name }}</td></tr>
        <tr><td class="label">Pengemudi</td>
            <td>{{ $booking->self_drive ? $booking->requester?->name.' (Self drive)' : $booking->driver?->name }}</td></tr>
        <tr><td class="label">Kontak</td>
            <td>{{ $booking->self_drive ? ($booking->requester?->phone ?: '-') : ($booking->driver?->phone ?: '-') }}</td></tr>
        @if ($booking->distance_traveled !== null)
            <tr><td class="label">Jarak tempuh</td><td>{{ number_format((int) $booking->distance_traveled, 0, ',', '.') }} km</td></tr>
        @endif
    </table>

    <h2 style="margin-top: 30px;">Kondisi Kendaraan</h2>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 25%"><strong>PEMERIKSAAN</strong></th>
                <th><strong>SERAH TERIMA</strong></th>
                <th><strong>PENGEMBALIAN</strong></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Waktu</strong></td>
                <td>{{ $booking->actual_start_datetime?->format('d M Y, H:i') ?: '-' }}</td>
                <td>{{ $booking->actual_end_datetime?->format('d M Y, H:i') ?: '-' }}</td>
            </tr>
            <tr>
                <td><strong>Odometer</strong></td>
                <td>{{ $booking->odometer_start !== null ? number_format((int) $booking->odometer_start, 0, ',', '.').' km' : '-' }}</td>
                <td>{{ $booking->odometer_end !== null ? number_format((int) $booking->odometer_end, 0, ',', '.').' km' : '-' }}</td>
            </tr>
            <tr>
                <td><strong>Level BBM</strong></td>
                <td>{{ $booking->fuel_level_start !== null ? round((float) $booking->fuel_level_start * 100).'%' : '-' }}</td>
                <td>{{ $booking->fuel_level_end !== null ? round((float) $booking->fuel_level_end * 100).'%' : '-' }}</td>
            </tr>
            <tr>
                <td><strong>Kelengkapan</strong></td>
                <td>{{ $checkoutChecklist ?: '-' }}</td>
                <td>{{ $checkinChecklist ?: '-' }}</td>
            </tr>
            <tr>
                <td style="border-bottom: none;"><strong>Catatan</strong></td>
                <td style="border-bottom: none;">{{ $booking->checkoutInspection?->condition_notes ?: '-' }}</td>
                <td style="border-bottom: none;">{{ $booking->checkinInspection?->condition_notes ?: '-' }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td class="title">Yang Menyerahkan (Admin GA)</td>
            <td class="title">Pengemudi</td>
            <td class="title">Pemohon</td>
        </tr>
        <tr>
            <td class="sign-space"></td>
            <td class="sign-space"></td>
            <td class="sign-space"></td>
        </tr>
        <tr>
            <td class="sign-line">{{ $booking->approver?->name ?? '(..............................)' }}</td>
            <td class="sign-line">
                {{ $booking->self_drive ? $booking->requester?->name : ($booking->driver?->name ?? '(..............................)') }}
            </td>
            <td class="sign-line">{{ $booking->requester?->name }}</td>
        </tr>
    </table>
@endsection
