<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reimbursement Telah Dibayar - USC Vehicle Ops</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8faf9; font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #475467;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8faf9; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; border: 1px solid #e8f6eb; box-shadow: 0 4px 20px rgba(16, 24, 40, 0.05); overflow: hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 40px 40px 20px 40px; border-top: 4px solid #1da338;">
                            <!-- Badge -->
                            <div style="display: inline-block; padding: 6px 14px; background-color: #e8f6eb; color: #1da338; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; border-radius: 20px; margin-bottom: 16px; border: 1px solid #c8eccf;">
                                Pembayaran Reimbursement
                            </div>
                            <h2 style="margin: 0; font-size: 26px; font-weight: 700; color: #02081C; letter-spacing: -0.5px;">Telah Dibayar</h2>
                            <p style="margin: 8px 0 0 0; font-size: 15px; font-weight: 500; color: #1da338;">USC Vehicle Ops</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 20px 40px 40px 40px; font-size: 15px; line-height: 1.6; color: #475467;">
                            <p style="margin: 0 0 20px 0;">Halo <strong>{{ $user->name ?? 'Pengguna' }}</strong>,</p>
                            <p style="margin: 0 0 20px 0;">Reimbursement batch Anda telah dibayar oleh tim Finance. Berikut rinciannya:</p>
                            
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px; border-radius: 8px; border: 1px solid #e2e8f0; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #02081C; width: 40%;">Nomor Batch</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #475467;">{{ $batch->batch_number }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #02081C;">Jumlah Klaim</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; color: #475467;">{{ $batch->item_count }} klaim</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; font-weight: 600; color: #02081C;">Total Dibayar</td>
                                    <td style="padding: 12px 16px; color: #1da338; font-weight: 700;">Rp {{ number_format((float) $batch->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            </table>

                            @if(filled($batch->payment_note))
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 20px; border-radius: 4px; overflow: hidden;">
                                <tr>
                                    <td style="background-color:#eff6ff;border-left:4px solid #3b82f6;padding:14px 16px">
                                        <p style="margin:0;color:#1e40af;font-size:13px;line-height:1.6">
                                            <strong>Catatan Pembayaran:</strong><br>
                                            {{ $batch->payment_note }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            @endif
                            
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="{{ $url }}" style="display: inline-block; padding: 10px 24px; background: linear-gradient(to right, #1da338, #10b981); background-color: #1da338; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 600; border-radius: 8px; box-shadow: 0 4px 15px rgba(29, 163, 56, 0.25);">Lihat Detail Batch</a>
                            </div>
                            
                            <p style="margin: 0;">Salam hangat,<br><span style="color: #02081C; font-weight: 600;">Tim USC</span></p>
                        </td>
                    </tr>

                    <!-- Footer Section inside card -->
                    <tr>
                        <td style="padding: 24px 40px; background-color: #fafdfb; border-top: 1px solid #f0fdf4; font-size: 13px; color: #667085; line-height: 1.5;">
                            <p style="margin: 0;">Jika Anda mengalami masalah saat mengklik tombol di atas, salin dan tempel URL berikut ke peramban (browser) Anda:</p>
                            <p style="margin: 10px 0 0 0; word-break: break-all;"><a href="{{ $url }}" style="color: #1da338; text-decoration: underline;">{{ $url }}</a></p>
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
