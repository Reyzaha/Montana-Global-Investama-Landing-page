<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — STRICT DUMMY CLEANUP SAFE RUNNER (CLI ONLY)
 * Menjalankan pembersihan data dummy dengan assertion rowCount() ketat.
 * Jika ada satupun langkah yang menghasilkan row count di luar ekspektasi,
 * sistem langsung menghentikan eksekusi dan melakukan ROLLBACK otomatis!
 *
 * Usage:
 *   php scripts/cleanup_dummy_safe_runner.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Akses ditolak: Skrip ini hanya dapat dijalankan melalui CLI interaktif.\n");
}

require_once __DIR__ . '/../backend/config/db.php';

echo "=================================================================\n";
echo "   PT MONTANA GLOBAL INVESTAMA — DUMMY CLEANUP SAFE RUNNER (CLI) \n";
echo "=================================================================\n\n";

try {
    $db = getDB();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "[✓] Berhasil terhubung ke database MySQL.\n";

    // 1. PRE-CHECK VALIDASI AKUN RIIL
    $stmtCheck16 = $db->query("SELECT COUNT(*) FROM investors WHERE id = 16");
    if ($stmtCheck16->fetchColumn() < 1) {
        die("[!] ABORT: Akun riil Investor ID 16 tidak ditemukan di database. Pembersihan dibatalkan demi keamanan.\n");
    }

    $stmtCheckAdmin = $db->query("SELECT COUNT(*) FROM admin_users WHERE id IN (1, 2)");
    if ($stmtCheckAdmin->fetchColumn() < 2) {
        die("[!] ABORT: Akun admin resmi (ID 1, 2) tidak lengkap. Pembersihan dibatalkan demi keamanan.\n");
    }

    echo "[✓] Pre-check validasi akun riil sukses: Investor ID 16 dan Admin resmi terkonfirmasi aman.\n\n";

    // 2. MULAI TRANSAKSI
    echo "[+] Membuka transaksi database (START TRANSACTION)...\n";
    $db->beginTransaction();

    $steps = [
        [
            'name' => '1. Financial Records Dummy',
            'sql' => "DELETE FROM financial_records 
                      WHERE id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 13, 14)
                        AND investor_id IN (1, 2, 5, 8, 9, 10, 11, 12, 13, 14, 15)
                        AND investor_id != 16
                        AND project_id = 'proj-jkt-jabar'
                        AND amount IN (5000000000, 2800000000, 350000000, 3150000000, 450000000, 85000000, 125000000, 520000000, 145000000, 500000000, 180000000, 88000000, 38000000)
                        AND (record_number LIKE 'CAP-%' OR record_number LIKE 'BILL-%' OR record_number LIKE 'AST-%' OR record_number LIKE 'INV-%' OR record_number LIKE 'EXP-%' OR record_number LIKE 'BUY-%')",
            'min' => 1,
            'max' => 13
        ],
        [
            'name' => '2. Dokumen Dummy (ID 9 Dikecualikan)',
            'sql' => "DELETE FROM project_documents 
                      WHERE id IN (1, 2, 3, 4, 5, 6, 7, 8, 10)
                        AND id != 9
                        AND investor_id IN (1, 2, 5, 8, 9, 10, 11, 12, 13, 14, 15)
                        AND investor_id != 16
                        AND project_id = 'proj-jkt-jabar'
                        AND doc_number LIKE 'DOC-%'",
            'min' => 1,
            'max' => 9
        ],
        [
            'name' => '3. Rekening Bank Dummy',
            'sql' => "DELETE FROM investor_bank_accounts 
                      WHERE id IN (1, 2)
                        AND investor_id IN (1, 2, 5, 8, 9, 10, 11, 12, 13, 14, 15)
                        AND investor_id != 16
                        AND bank_name IN ('BCA Prioritas', 'Bank Mandiri')",
            'min' => 1,
            'max' => 2
        ],
        [
            'name' => '4. Profil Perusahaan Dummy',
            'sql' => "DELETE FROM investor_companies 
                      WHERE id = 1
                        AND investor_id IN (1, 2, 5, 8, 9, 10, 11, 12, 13, 14, 15)
                        AND investor_id != 16
                        AND business_name = 'PT Nusantara Capital Group'",
            'min' => 1,
            'max' => 1
        ],
        [
            'name' => '5. Portofolio Dummy',
            'sql' => "DELETE FROM investor_portfolios 
                      WHERE investor_id IN (1, 2, 5, 8, 9, 10, 11, 12, 13, 14, 15)
                        AND investor_id != 16
                        AND project_id = 'proj-jkt-jabar'
                        AND (
                            (id = 1 AND amount = 5000000000) OR
                            (id = 2 AND amount = 2500000000)
                        )",
            'min' => 1,
            'max' => 2
        ],
        [
            'name' => '6. Unit Inventory Seed Awal (EXC-AB-04 Dikecualikan)',
            'sql' => "DELETE FROM project_inventory 
                      WHERE id IN (1, 2, 3)
                        AND id != 4
                        AND item_code IN ('EXC-KM-138-01', 'EXC-KM-138-02', 'ATT-BRK-001')",
            'min' => 1,
            'max' => 3
        ],
        [
            'name' => '7. Akun Investor Dummy',
            'sql' => "DELETE FROM investors 
                      WHERE id IN (1, 2, 5, 8, 9, 10, 11, 12, 13, 14, 15)
                        AND (email IN ('investor@gmail.com', 'corporate@holding.com') OR email LIKE 'unit_test_%' OR email LIKE '%@mgi-testing.com')
                        AND id != 16",
            'min' => 1,
            'max' => 11
        ]
    ];

    foreach ($steps as $step) {
        $stmt = $db->prepare($step['sql']);
        $stmt->execute();
        $affected = $stmt->rowCount();

        echo sprintf("  %-50s : %d baris terhapus\n", $step['name'], $affected);

        if ($affected < $step['min'] || $affected > $step['max']) {
            $db->rollBack();
            echo "\n[✕] ANOMALI TERDETEKSI PADA {$step['name']}!\n";
            echo "    Baris terhapus: {$affected} (Ekspektasi batas: {$step['min']} s/d {$step['max']})\n";
            echo "[!] TRANSAKSI TELAH DI-ROLLBACK OTOMATIS! TIDAK ADA DATA YANG DIUBAH.\n";
            exit(1);
        }
    }

    // 3. POST-CHECK INTEGRITAS
    $check16 = (int)$db->query("SELECT COUNT(*) FROM investors WHERE id = 16")->fetchColumn();
    $checkAdmins = (int)$db->query("SELECT COUNT(*) FROM admin_users WHERE id IN (1, 2)")->fetchColumn();
    $checkUnit4 = (int)$db->query("SELECT COUNT(*) FROM project_inventory WHERE id = 4 AND item_code = 'EXC-AB-04'")->fetchColumn();
    $checkDoc9 = (int)$db->query("SELECT COUNT(*) FROM project_documents WHERE id = 9")->fetchColumn();
    $checkDummyInv = (int)$db->query("SELECT COUNT(*) FROM investors WHERE id IN (1, 2, 5, 8, 9, 10, 11, 12, 13, 14, 15)")->fetchColumn();

    if ($check16 !== 1 || $checkAdmins < 2 || $checkUnit4 !== 1 || $checkDoc9 !== 1 || $checkDummyInv !== 0) {
        $db->rollBack();
        echo "\n[✕] POST-CHECK GAGAL!\n";
        echo "    - Investor ID 16: {$check16} (wajib 1)\n";
        echo "    - Admin Resmi: {$checkAdmins} (wajib >= 2)\n";
        echo "    - Unit EXC-AB-04: {$checkUnit4} (wajib 1)\n";
        echo "    - Dokumen ID 9: {$checkDoc9} (wajib 1)\n";
        echo "    - Sisa Dummy Investors: {$checkDummyInv} (wajib 0)\n";
        echo "[!] TRANSAKSI TELAH DI-ROLLBACK OTOMATIS!\n";
        exit(1);
    }

    echo "\n[✓] Seluruh rowCount() dan post-check integritas 100% VALID & SESUAI TARGET!\n";
    echo "=================================================================\n";
    echo "KONFIRMASI EKSEKUSI FINAL:\n";
    echo "Perubahan saat ini masih berada di dalam buffer transaksi sementara.\n";
    echo "Ketik 'COMMIT' persis huruf besar untuk menyimpan secara permanen,\n";
    echo "atau tekan ENTER / ketik lainnya untuk membatalkan (ROLLBACK): ";

    $confirm = trim(fgets(STDIN));
    if ($confirm === 'COMMIT') {
        $db->commit();
        echo "\n[✓] TRANSAKSI BERHASIL DI-COMMIT! Data dummy telah resmi dibersihkan dari database.\n\n";
    } else {
        $db->rollBack();
        echo "\n[!] Konfirmasi tidak sesuai ('{$confirm}'). Transaksi telah di-ROLLBACK!\n";
        echo "[!] Tidak ada perubahan yang disimpan ke database produksi.\n\n";
    }

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "\n[✕] TERJADI KESALAHAN SISTEM: " . $e->getMessage() . "\n";
    echo "[!] TRANSAKSI TELAH DI-ROLLBACK OTOMATIS!\n";
    exit(1);
}
