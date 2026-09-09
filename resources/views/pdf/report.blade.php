{{-- FR-M6-05 — export PDF laporan (kolom mengikuti jenis laporan). --}}
@extends('pdf.layout')

@section('title', $title)

@section('doc-number')
    <span class="muted">Periode</span><br>
    <strong>{{ \Illuminate\Support\Carbon::parse($period['from'])->format('d-m-Y') }}
        s.d. {{ \Illuminate\Support\Carbon::parse($period['to'])->format('d-m-Y') }}</strong>
@endsection

@section('content')
    <table class="data">
        <thead>
            <tr>
                @foreach ($columns as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach (array_keys($columns) as $key)
                        @php $value = $row[$key] ?? null; @endphp
                        <td class="{{ is_numeric($value) ? 'text-right' : '' }}">
                            {{ is_numeric($value) ? number_format((float) $value, fmod((float) $value, 1) !== 0.0 ? 2 : 0, ',', '.') : ($value ?: '-') }}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}">Tidak ada data pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if (!empty($totals))
            <tfoot>
                <tr>
                    @php $first = true; @endphp
                    @foreach (array_keys($columns) as $key)
                        @if ($first)
                            <td><strong>TOTAL</strong></td>
                            @php $first = false; @endphp
                        @else
                            @php $val = $totals[$key] ?? ''; @endphp
                            <td class="{{ is_numeric($val) ? 'text-right' : '' }}">
                                <strong>{{ is_numeric($val) ? number_format((float) $val, fmod((float) $val, 1) !== 0.0 ? 2 : 0, ',', '.') : $val }}</strong>
                            </td>
                        @endif
                    @endforeach
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
