{{-- FR-M4-17 — email permintaan servis ke PIC vendor. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan Servis Kendaraan - USC Vehicle Ops</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8faf9; font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #475467;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8faf9; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; border: 1px solid #e8f6eb; box-shadow: 0 4px 20px rgba(16, 24, 40, 0.05); overflow: hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 40px 40px 20px 40px; border-top: 4px solid #1da338; text-align: center;">
                            <!-- Badge -->
                            <div style="display: inline-block; padding: 6px 14px; background-color: #e8f6eb; color: #1da338; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; border-radius: 20px; margin-bottom: 16px; border: 1px solid #c8eccf;">
                                Pengajuan Servis Baru
                            </div>
                            <h2 style="margin: 0; font-size: 24px; font-weight: 700; color: #02081C; letter-spacing: -0.5px; text-align: center;">Permintaan Servis Kendaraan</h2>
                            <p style="margin: 8px 0 0 0; font-size: 15px; font-weight: 500; color: #1da338; text-align: center;">USC Vehicle Ops</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 20px 40px 40px 40px; font-size: 15px; line-height: 1.6; color: #475467;">
                            <p style="margin: 0 0 20px 0;">Kepada Yth. <strong>{{ $request->vendor?->pic_name ?: $request->vendor?->name }}</strong>,</p>
                            <p style="margin: 0 0 20px 0;">Bersama ini kami sampaikan permintaan servis untuk kendaraan operasional kami. Berikut adalah rinciannya:</p>
                            
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px; border-radius: 8px; border: 1px solid #e2e8f0; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #02081C; width: 40%;">Nomor Permintaan</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #475467;">{{ $request->request_number }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #02081C;">Nomor Polisi</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #475467;">{{ $request->vehicle?->plate_number }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #02081C;">Kendaraan</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #475467;">{{ $request->vehicle?->full_name }} ({{ $request->vehicle?->year }})</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #02081C;">Jenis Servis</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #475467;">{{ $request->serviceType?->name ?? 'Pemeriksaan umum' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #02081C;">Odometer Saat Ini</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #475467;">{{ number_format((int) $request->current_odometer, 0, ',', '.') }} km</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #02081C;">KM Jatuh Tempo</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #475467;">{{ $request->due_odometer ? number_format((int) $request->due_odometer, 0, ',', '.').' km' : '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; font-weight: 600; color: #02081C;">Tanggal</td>
                                    <td style="padding: 12px 16px; color: #475467;">{{ $request->created_at?->format('d-m-Y') }}</td>
                                </tr>
                            </table>

                            @if (filled($request->complaint_note))
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 20px; border-radius: 4px; overflow: hidden;">
                                <tr>
                                    <td style="background-color:#eff6ff;border-left:4px solid #3b82f6;padding:14px 16px">
                                        <p style="margin:0;color:#1e40af;font-size:13px;line-height:1.6">
                                            <strong>Catatan Keluhan:</strong><br>
                                            {{ $request->complaint_note }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            @endif
                            
                            <p style="margin: 0 0 20px 0;">Mohon konfirmasi jadwal pengerjaan melalui kontak Admin GA kami. Rincian permintaan juga terlampir dalam berkas PDF pada email ini.</p>
                            
                            <p style="margin: 0;">Terima kasih atas kerja samanya.<br><br>Salam,<br><span style="color: #02081C; font-weight: 600;">{{ config('USC Vehicle Ops.company.name', 'Tim USC') }}</span></p>
                        </td>
                    </tr>

                </table>

                <!-- Footer Outside Card -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                    <tr>
                        <td align="center" style="padding: 20px 0; font-size: 12px; color: #98a2b3;">
                            <p style="margin: 0;">&copy; {{ date('Y') }} USC Features &middot; Hak Cipta Dilindungi</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
