<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — SECURE ADMIN PASSWORD & MFA ROTATION
 * Interactive CLI-only utility to rotate admin credentials without exposing
 * plain passwords in command line arguments, shell history, or chat logs.
 *
 * Usage on Server (Docker):
 *   docker exec -it mgi_web_app php scripts/rotate_admin_interactive.php
 *
 * Usage on Direct Linux VPS:
 *   php scripts/rotate_admin_interactive.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Akses ditolak: Skrip ini hanya dapat dijalankan melalui CLI interaktif.\n");
}

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/auth_helper.php';

// Helper fungsi echo terminal aman
function enableEcho(): void {
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        @shell_exec('stty echo 2>/dev/null');
    } else {
        @exec('stty echo 2>nul');
    }
}

function disableEcho(): void {
    $out = [];
    $code = 1;
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows test stty
        @exec('stty -echo 2>&1', $out, $code);
        if ($code !== 0) {
            echo "\n[!] PERINGATAN KEAMANAN: Terminal ini tidak mendukung penonaktifan echo (stty -echo).\n";
            echo "    Untuk mencegah password tampil di layar, jalankan skrip ini dari terminal Linux/bash atau via Docker:\n";
            echo "    docker exec -it mgi_web_app php scripts/rotate_admin_interactive.php\n\n";
            exit(1);
        }
    } else {
        @exec('stty -echo 2>&1', $out, $code);
        if ($code !== 0) {
            echo "\n[!] ERROR: Gagal menonaktifkan echo terminal (stty -echo return status {$code}). Skrip ditolak demi keamanan.\n";
            exit(1);
        }
    }
}

// Pulihkan echo terminal di semua jalur keluar tak terduga (shutdown & SIGINT)
register_shutdown_function('enableEcho');
if (function_exists('pcntl_signal') && function_exists('pcntl_async_signals')) {
    pcntl_async_signals(true);
    pcntl_signal(SIGINT, function() {
        enableEcho();
        echo "\n[!] Operasi dibatalkan oleh pengguna (SIGINT).\n";
        exit(130);
    });
}

echo "=================================================================\n";
echo "   PT MONTANA GLOBAL INVESTAMA — ROTASI KREDENSIAL ADMIN AMAN   \n";
echo "=================================================================\n\n";

try {
    $db = getDB();
    echo "[✓] Berhasil terhubung ke database MySQL.\n\n";

    // 1. Pilih Akun yang Ingin Dirotasi
    echo "Pilih akun admin yang ingin dirotasi:\n";
    echo "  1. superadmin (Super Administrator MGI)\n";
    echo "  2. admin (Administrator Operasional MGI)\n";
    echo "  3. KEDUA AKUN SEKALIGUS (superadmin & admin)\n";
    echo "Pilihan (1/2/3): ";
    
    $choice = trim(fgets(STDIN));
    $targets = [];
    if ($choice === '1') {
        $targets = ['superadmin'];
    } elseif ($choice === '2') {
        $targets = ['admin'];
    } elseif ($choice === '3') {
        $targets = ['superadmin', 'admin'];
    } else {
        echo "[!] Pilihan tidak valid. Dibatalkan.\n";
        exit(1);
    }

    // 2. Input Password Baru Secara Interaktif Tanpa Echo
    echo "\nMasukkan password baru (minimal 12 karakter): ";
    try {
        disableEcho();
        $password = trim(fgets(STDIN));
    } finally {
        enableEcho();
        echo "\n";
    }

    if (strlen($password) < 12) {
        echo "[!] ERROR: Password harus minimal 12 karakter untuk keamanan standar PCI-DSS/Fintech.\n";
        exit(1);
    }

    echo "Konfirmasi password baru sekali lagi: ";
    try {
        disableEcho();
        $passwordConfirm = trim(fgets(STDIN));
    } finally {
        enableEcho();
        echo "\n";
    }

    if ($password !== $passwordConfirm) {
        echo "[!] ERROR: Konfirmasi password tidak cocok! Dibatalkan.\n";
        exit(1);
    }

    // 3. Hash dengan Bcrypt Cost 10
    $hash = hashPassword($password);

    // 4. Konfirmasi Eksplisit Sebelum Menulis ke Database
    echo "\nPERINGATAN: Kredensial akun [" . implode(', ', $targets) . "] akan diperbarui dan status MFA akan di-reset.\n";
    echo "Apakah Anda yakin ingin menyimpan perubahan ini ke database? (Ketik 'YA' untuk melanjutkan): ";
    $confirmExec = trim(fgets(STDIN));
    if ($confirmExec !== 'YA') {
        echo "[!] Operasi dibatalkan. Tidak ada perubahan yang disimpan ke database.\n";
        exit(0);
    }

    // 5. Update Database: Ganti Password, Reset MFA Secret
    $stmt = $db->prepare("
        UPDATE admin_users 
        SET password_hash = ?, 
            mfa_enabled = 0, 
            mfa_secret = NULL, 
            updated_at = NOW() 
        WHERE username = ?
    ");

    $stmtLog = $db->prepare("
        INSERT INTO admin_logs (admin_id, action, target_type, target_id, details, ip_address)
        SELECT id, 'password_rotated', 'admin_users', username, 'Credentials rotated and MFA reset via secure CLI utility', '127.0.0.1 (CLI)'
        FROM admin_users WHERE username = ?
    ");

    foreach ($targets as $user) {
        $stmt->execute([$hash, $user]);
        try {
            $stmtLog->execute([$user]);
        } catch (Exception $eLog) {
            // Silently continue if log fails
        }
        echo "[✓] Password {$user} berhasil diperbarui. MFA di-reset. Audit log 'password_rotated' tercatat.\n";
    }

    // 6. Bersihkan HANYA rate limit login admin (TIDAK menyentuh rate limit fitur lain)
    try {
        $checkTbl = $db->query("SHOW TABLES LIKE 'rate_limits'")->fetch();
        if ($checkTbl) {
            $cols = $db->query("DESCRIBE rate_limits")->fetchAll(PDO::FETCH_COLUMN);
            $keyCol = in_array('action_key', $cols) ? 'action_key' : (in_array('key', $cols) ? 'key' : (in_array('name', $cols) ? 'name' : null));
            if ($keyCol) {
                $stmtClean = $db->prepare("DELETE FROM rate_limits WHERE {$keyCol} LIKE 'admin_login%' OR {$keyCol} LIKE 'admin_mfa%'");
                $stmtClean->execute();
                $deletedCount = $stmtClean->rowCount();
                echo "[✓] Tabel rate_limits: {$deletedCount} baris entri admin berhasil dibersihkan.\n";
            } else {
                echo "[i] Tabel rate_limits ditemukan tanpa kolom key yang cocok. Dilewati.\n";
            }
        } else {
            echo "[i] Tabel 'rate_limits' tidak ada di database (Rate limit dikelola via Session PHP per-klien).\n";
        }
    } catch (Exception $e) {
        echo "[i] Pembersihan rate_limits dilewati: " . $e->getMessage() . "\n";
    }

    echo "\n-----------------------------------------------------------------\n";
    echo "[!] PENTING - TINDAKAN SEGERA SETELAH INI:\n";
    echo "1. Buka browser ke: https://montanainvestama.com/portal-admin-mgi-gateway/login.php\n";
    echo "2. Login menggunakan username dan password baru yang baru saja Anda buat.\n";
    echo "3. Scan QR Code Google Authenticator yang muncul di layar dan masukkan 6-digit OTP.\n";
    echo "4. 2FA akan langsung aktif kembali secara permanen dengan secret yang baru!\n";
    echo "-----------------------------------------------------------------\n\n";

} catch (Exception $e) {
    echo "[!] Terjadi kesalahan: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    enableEcho();
}
