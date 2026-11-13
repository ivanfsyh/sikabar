<?php

namespace App\Services;

use App\Models\Setting;

class PengaturanMail
{
    /** Terapkan pengaturan email dari database ke konfigurasi Laravel saat runtime. */
    public static function terapkan(): void
    {
        if ($dari = Setting::get('email_dari_alamat')) {
            config(['mail.from.address' => $dari]);
        }
        if ($nama = Setting::get('email_dari_nama')) {
            config(['mail.from.name' => $nama]);
        }

        if ($host = Setting::get('smtp_host')) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => (int) Setting::get('smtp_port', 587),
                'mail.mailers.smtp.username' => Setting::get('smtp_username'),
                'mail.mailers.smtp.password' => Setting::get('smtp_password'),
                // ssl (port 465) = smtps; tls (port 587) = smtp dengan STARTTLS otomatis
                'mail.mailers.smtp.scheme' => Setting::get('smtp_enkripsi', 'tls') === 'ssl' ? 'smtps' : 'smtp',
            ]);
        }

        app('mail.manager')->purge('smtp');
    }
}
