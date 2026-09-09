{{-- Tata letak dasar seluruh dokumen PDF USC Vehicle Ops (FR-M2-26, FR-M3-11, FR-M4-17). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Dokumen USC Vehicle Ops')</title>
    <style>
        @page { margin: 15mm 18mm 15mm; }
        body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 11px; color: #374151; line-height: 1.4; }
        h1 { font-size: 11px; color: #374151; margin: 0 0 15px; font-weight: normal; text-transform: capitalize; }
        h2 { font-size: 12px; color: #0e993b; margin: 15px 0 8px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; padding-bottom: 4px; }
        
        .header { width: 100%; border-bottom: 3px solid #000000; padding-bottom: 10px; margin-bottom: 2px; }
        .header table { margin: 0; width: 100%; }
        .header td { vertical-align: middle; }
        .company-name { font-size: 18px; font-weight: 900; color: #02081c; margin-bottom: 2px; letter-spacing: -0.5px; }
        .company-desc { font-size: 10px; color: #6b7280; font-weight: 500; }
        
        .doc-number { text-align: right; width: 35%; }
        .doc-number strong { font-size: 14px; color: #02081c; font-weight: 800; }
        .doc-number .muted { font-size: 10px; color: #9ca3af; font-weight: 600; display: block; margin-top: 2px; }
        
        table { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 10px; }
        th, td { text-align: left; vertical-align: top; }
        
        /* Modern Data Table */
        table.data { border: 1px solid #e5e7eb; border-radius: 6px; overflow: hidden; }
        table.data th { background: #f9fafb; color: #4b5563; padding: 8px 10px; font-size: 10px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; }
        table.data td { border-bottom: 1px solid #f3f4f6; padding: 7px 10px; font-size: 11px; }
        table.data tr:last-child td { border-bottom: none; }
        table.data tbody tr:nth-child(even) { background-color: #fafbfc; }
        
        /* Clean Meta Table */
        table.meta { margin-bottom: 0; }
        table.meta td { padding: 4px 0; font-size: 11px; border-bottom: 1px dashed #e5e7eb; }
        table.meta tr:last-child td { border-bottom: none; }
        table.meta td.label { width: 35%; color: #6b7280; font-weight: 600; }
        table.meta td strong { color: #111827; font-weight: 700; }
        
        /* Signatures */
        .signatures { width: 100%; margin-top: 40px; page-break-inside: avoid; }
        .signatures td { text-align: center; width: 33.33%; padding: 0 10px; }
        .signatures td.title { color: #6b7280; font-weight: 700; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .sign-space { height: 80px; }
        .sign-line { padding-top: 4px; font-weight: 700; font-size: 11px; color: #111827; }
        
        .footer-note { position: absolute; bottom: 10mm; left: 0; right: 0; font-size: 9px; color: #9ca3af; text-align: right; font-style: italic; border-top: 1px solid #f3f4f6; padding-top: 6px; font-weight: 500; }
    </style>
</head>
<body>
    @php
        $logoPath = app(\App\Services\SettingService::class)->get('company_logo_path');
        $logoSrc = null;
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $mime = Storage::disk('public')->mimeType($logoPath);
            $logoData = base64_encode(Storage::disk('public')->get($logoPath));
            $logoSrc = 'data:' . $mime . ';base64,' . $logoData;
        }
    @endphp
    <div class="header">
        <table>
            <tr>
                @if ($logoSrc)
                    <td style="width: 70px; padding-right: 15px;">
                        <img src="{{ $logoSrc }}" alt="Logo" style="max-width: 70px; max-height: 70px;">
                    </td>
                @endif
                <td style="padding-right: 20px; width: 50%;">
                    <div class="company-name">{{ app(\App\Services\SettingService::class)->get('company_name') ?: ($company['name'] ?? config('USC Vehicle Ops.company.name', 'PT USC Indonesia')) }}</div>
                    <div class="company-desc">{{ app(\App\Services\SettingService::class)->get('company_address') ?: ($company['address'] ?? config('USC Vehicle Ops.company.address', 'Kawasan Industri Mitra Karawang (KIM), Jl. Mitra Raya Selatan II Blok F No. 1, Parungmulya, Ciampel, Kabupaten Karawang, Jawa Barat 41361')) }}</div>
                </td>
                <td class="doc-number">
                    @yield('doc-number')
                </td>
            </tr>
        </table>
    </div>
    <!-- DOMPDF shadow workaround -->
    <div style="border-top: 1px solid #cbd5e1; margin-bottom: 1px;"></div>
    <div style="border-top: 1px solid #e2e8f0; margin-bottom: 1px;"></div>
    <div style="border-top: 1px solid #f1f5f9; margin-bottom: 15px;"></div>

    <h1 style="margin-bottom: 12px;">Perihal : @yield('title')</h1>

    @yield('content')

    <div class="footer-note">
        Dicetak dari sistem USC Vehicle Ops pada {{ now()->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB.
    </div>
</body>
</html>

