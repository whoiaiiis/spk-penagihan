<?php

use Illuminate\Support\Facades\Session;

if (!function_exists('has_permission')) {
    /**
     * Check if the currently logged-in user has the specified permission.
     * Admins automatically have all permissions.
     *
     * @param string $permission The permission string (e.g. 'pelanggan:view')
     * @return bool
     */
    function has_permission(string $permission): bool
    {
        $role = Session::get('role');

        // Admin has full access to everything
        if ($role && strtolower($role) === 'admin') {
            return true;
        }

        $permissions = Session::get('permissions') ?? [];

        // Check if the permission is in the user's permissions array
        return in_array($permission, $permissions);
    }
}

if (!function_exists('hasPermission')) {
    /**
     * Alias for has_permission.
     *
     * @param string $permission
     * @return bool
     */
    function hasPermission(string $permission): bool
    {
        return has_permission($permission);
    }
}

if (!function_exists('periode_ke_nama_file')) {
    /**
     * Konversi string periode format "Y-m" menjadi nama file export.
     * Nama bulan ditampilkan dalam Bahasa Indonesia.
     *
     * Contoh:
     *   periode_ke_nama_file('2026-01')  => 'Januari_2026'
     *   periode_ke_nama_file('2026-02')  => 'Februari_2026'
     *   periode_ke_nama_file(null)       => 'Semua_Periode'
     *   periode_ke_nama_file('')         => 'Semua_Periode'
     *
     * @param string|null $periode  Format Y-m (contoh: '2026-02')
     * @return string
     */
    function periode_ke_nama_file(?string $periode): string
    {
        if (empty($periode)) {
            return 'Semua_Periode';
        }

        $bulanId = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        // Pisahkan tahun dan bulan dari format "Y-m"
        $parts = explode('-', $periode);
        if (count($parts) !== 2) {
            return 'Semua_Periode';
        }

        [$tahun, $bulan] = $parts;

        $namaBulan = $bulanId[$bulan] ?? null;
        if (!$namaBulan) {
            return 'Semua_Periode';
        }

        return $namaBulan . '_' . $tahun;
    }
}

if (!function_exists('periode_ke_label')) {
    /**
     * Konversi string periode format "Y-m" menjadi label teks untuk header laporan.
     * Nama bulan ditampilkan dalam Bahasa Indonesia dengan spasi (bukan underscore).
     *
     * Contoh:
     *   periode_ke_label('2026-02')  => 'Februari 2026'
     *   periode_ke_label(null)       => 'Semua Periode'
     *
     * @param string|null $periode  Format Y-m (contoh: '2026-02')
     * @return string
     */
    function periode_ke_label(?string $periode): string
    {
        return str_replace('_', ' ', periode_ke_nama_file($periode));
    }
}
