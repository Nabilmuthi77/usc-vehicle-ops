<?php

namespace App\Support;

use App\Models\User;

/**
 * PRD §5.2 — matriks hak akses USC_VEHICLE_OPS.
 *
 * Daftar permission granular beserta pemetaannya ke peran, dipakai oleh
 * RolePermissionSeeder dan sebagai konstanta di Policy/Gate/Blade.
 */
class Permissions
{
    // --- M1 Master data & administrasi ---
    public const VEHICLE_VIEW = 'kendaraan.lihat';

    public const VEHICLE_CREATE = 'kendaraan.tambah';

    public const VEHICLE_UPDATE = 'kendaraan.ubah';

    public const VEHICLE_DELETE = 'kendaraan.hapus';

    public const MASTER_DATA_VIEW = 'master-data.lihat';

    public const MASTER_DATA_MANAGE = 'master-data.kelola';

    public const USER_MANAGE = 'pengguna.kelola';

    public const SETTING_MANAGE = 'pengaturan.kelola';

    public const AUDIT_LOG_VIEW = 'audit-log.lihat';

    // --- M2 Peminjaman ---
    public const BOOKING_VIEW_ALL = 'peminjaman.lihat-semua';

    public const BOOKING_CREATE = 'peminjaman.ajukan';

    public const BOOKING_APPROVE = 'peminjaman.setujui';

    public const BOOKING_HANDOVER = 'peminjaman.serah-terima';

    public const BOOKING_CORRECT = 'peminjaman.koreksi';

    // --- M3 BBM ---
    public const FUEL_CLAIM_CREATE = 'bbm.ajukan-klaim';

    public const FUEL_CLAIM_VERIFY = 'bbm.verifikasi-klaim';

    public const FUEL_VIEW_ALL = 'bbm.lihat-semua';

    public const REIMBURSEMENT_MANAGE = 'reimbursement.kelola';

    public const REIMBURSEMENT_PAY = 'reimbursement.tandai-dibayar';

    // --- M4 Servis ---
    public const SERVICE_VIEW = 'servis.lihat';

    public const SERVICE_MANAGE = 'servis.kelola';

    public const SERVICE_REQUEST_MANAGE = 'permintaan-servis.kelola';

    // --- M5 Tol ---
    public const TOLL_VIEW = 'tol.lihat';

    public const TOLL_MANAGE = 'tol.kelola';

    // --- M6 Laporan ---
    public const REPORT_VIEW = 'laporan.lihat';

    public const REPORT_EXPORT = 'laporan.export';

    /** @return array<int, string> seluruh permission yang dikenal sistem. */
    public static function all(): array
    {
        return [
            self::VEHICLE_VIEW,
            self::VEHICLE_CREATE,
            self::VEHICLE_UPDATE,
            self::VEHICLE_DELETE,
            self::MASTER_DATA_VIEW,
            self::MASTER_DATA_MANAGE,
            self::USER_MANAGE,
            self::SETTING_MANAGE,
            self::AUDIT_LOG_VIEW,
            self::BOOKING_VIEW_ALL,
            self::BOOKING_CREATE,
            self::BOOKING_APPROVE,
            self::BOOKING_HANDOVER,
            self::BOOKING_CORRECT,
            self::FUEL_CLAIM_CREATE,
            self::FUEL_CLAIM_VERIFY,
            self::FUEL_VIEW_ALL,
            self::REIMBURSEMENT_MANAGE,
            self::REIMBURSEMENT_PAY,
            self::SERVICE_VIEW,
            self::SERVICE_MANAGE,
            self::SERVICE_REQUEST_MANAGE,
            self::TOLL_VIEW,
            self::TOLL_MANAGE,
            self::REPORT_VIEW,
            self::REPORT_EXPORT,
        ];
    }

    /**
     * Pemetaan peran → permission sesuai matriks PRD §5.2.
     *
     * @return array<string, array<int, string>>
     */
    public static function matrix(): array
    {
        return [
            // Admin Sistem — CRUD penuh atas master data & pengguna.
            User::ROLE_ADMIN_SISTEM => self::all(),

            // Admin GA — pengelola armada harian, satu-satunya approver.
            User::ROLE_ADMIN_GA => [
                self::VEHICLE_VIEW,
                self::VEHICLE_CREATE,
                self::VEHICLE_UPDATE,
                self::VEHICLE_DELETE,
                self::MASTER_DATA_VIEW,
                self::MASTER_DATA_MANAGE,
                self::AUDIT_LOG_VIEW,
                self::BOOKING_VIEW_ALL,
                self::BOOKING_CREATE,
                self::BOOKING_APPROVE,
                self::BOOKING_HANDOVER,
                self::FUEL_CLAIM_CREATE,
                self::FUEL_CLAIM_VERIFY,
                self::FUEL_VIEW_ALL,
                self::REIMBURSEMENT_MANAGE,
                self::REIMBURSEMENT_PAY,
                self::SERVICE_VIEW,
                self::SERVICE_MANAGE,
                self::SERVICE_REQUEST_MANAGE,
                self::TOLL_VIEW,
                self::TOLL_MANAGE,
                self::REPORT_VIEW,
                self::REPORT_EXPORT,
            ],

            // Karyawan — mengajukan peminjaman dan klaim BBM miliknya sendiri.
            User::ROLE_KARYAWAN => [
                self::BOOKING_CREATE,
                self::FUEL_CLAIM_CREATE,
            ],

            // Driver — input odometer, nota BBM, dan transaksi tol.
            User::ROLE_DRIVER => [
                self::VEHICLE_VIEW,
                self::BOOKING_CREATE,
                self::BOOKING_HANDOVER,
                self::FUEL_CLAIM_CREATE,
                self::TOLL_VIEW,
                self::TOLL_MANAGE,
            ],

            // Viewer (Direksi/Finance) — read-only + penandaan pembayaran.
            User::ROLE_VIEWER => [
                self::VEHICLE_VIEW,
                self::MASTER_DATA_VIEW,
                self::BOOKING_VIEW_ALL,
                self::FUEL_VIEW_ALL,
                self::REIMBURSEMENT_PAY,
                self::SERVICE_VIEW,
                self::TOLL_VIEW,
                self::REPORT_VIEW,
                self::REPORT_EXPORT,
            ],
        ];
    }

    /** @return array<int, string> */
    public static function roles(): array
    {
        return array_keys(self::matrix());
    }
}
