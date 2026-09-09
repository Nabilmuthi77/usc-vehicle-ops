{{-- FR-M3-11 — rekap klaim reimbursement BBM untuk diserahkan ke Finance. --}}
@extends('pdf.layout')

@section('title', 'Rekap Klaim Reimbursement BBM')

@section('doc-number')
    <strong>{{ $batch->batch_number }}</strong><br>
    <span class="muted">{{ $batch->created_at?->format('d-m-Y') }}</span>
@endsection

@section('content')
    <table class="meta">
        <tr><td class="label">Nama pengaju</td><td>{{ $batch->claimant?->name }}</td></tr>
        <tr><td class="label">Departemen</td><td>{{ $batch->claimant?->department?->name ?: '-' }}</td></tr>
        <tr><td class="label">Periode</td>
            <td>{{ $batch->period_start->format('d-m-Y') }} s.d. {{ $batch->period_end->format('d-m-Y') }}</td></tr>
        <tr><td class="label">Status</td><td>{{ $batch->status->label() }}</td></tr>
    </table>

    <h2>Rincian Nota</h2>
    <table class="data">
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th style="width:14%">Tanggal</th>
                <th style="width:14%">Kendaraan</th>
                <th>SPBU</th>
                <th style="width:14%">No. Nota</th>
                <th style="width:10%" class="text-right">Liter</th>
                <th style="width:16%" class="text-right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($batch->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->transaction_datetime->format('d-m-Y') }}</td>
                    <td>{{ $item->vehicle?->plate_number }}</td>
                    <td>{{ $item->station_name }}</td>
                    <td>{{ $item->receipt_number }}</td>
                    <td class="text-right">{{ number_format((float) $item->liters, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->claimable_amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" class="text-right">Total ({{ (int) $batch->item_count }} nota)</td>
                <td class="text-right">Rp {{ number_format((float) $batch->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p class="muted">
        Terbilang: {{ $terbilang }} rupiah.
    </p>

    <table class="signatures">
        <tr>
            <td>Pemohon</td>
            <td>Diverifikasi Admin GA</td>
            <td>Disetujui Finance</td>
        </tr>
        <tr>
            <td class="sign-space"></td>
            <td class="sign-space"></td>
            <td class="sign-space"></td>
        </tr>
        <tr>
            <td class="sign-line">{{ $batch->claimant?->name }}</td>
            <td class="sign-line">{{ $batch->createdBy?->name ?: '(..............................)' }}</td>
            <td class="sign-line">{{ $batch->paidBy?->name ?: '(..............................)' }}</td>
        </tr>
    </table>
@endsection
