<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN CONTACT INQUIRIES
 * Methods: GET, PUT, DELETE, GET ?export=csv
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$admin = requireAdminAuth();
$method = $_SERVER['REQUEST_METHOD'];
$input = getJsonInput();

if (isset($input['_method'])) {
    $method = strtoupper($input['_method']);
} elseif (isset($_GET['_method'])) {
    $method = strtoupper($_GET['_method']);
}

try {
    $db = getDB();

    // 1. GET: Ambil daftar pesan masuk atau CSV export
    if ($method === 'GET') {
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $export = isset($_GET['export']) ? trim($_GET['export']) : '';

        $sql = "SELECT * FROM contact_inquiries WHERE 1=1";
        $params = [];

        if ($status !== '' && in_array($status, ['unread', 'read', 'contacted', 'archived'])) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        if ($search !== '') {
            $sql .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR subject LIKE ? OR message LIKE ?)";
            $term = '%' . $search . '%';
            array_push($params, $term, $term, $term, $term, $term);
        }

        $sql .= " ORDER BY created_at DESC LIMIT 1000";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $inquiries = $stmt->fetchAll();

        // Export to CSV
        if ($export === 'csv') {
            while (ob_get_level() > 0) ob_end_clean();
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="pesan-masuk-mgi-' . date('Ymd-His') . '.csv"');
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($out, ['ID', 'Waktu Masuk', 'Nama Lengkap', 'Email', 'No Telepon', 'Subjek', 'Isi Pesan', 'Status', 'IP Address']);
            foreach ($inquiries as $row) {
                fputcsv($out, [
                    $row['id'],
                    $row['created_at'],
                    $row['full_name'],
                    $row['email'],
                    $row['phone'],
                    $row['subject'],
                    $row['message'],
                    $row['status'],
                    $row['ip_address']
                ]);
            }
            fclose($out);
            logAdminActivity('export_inquiries', 'contact_inquiries', null, 'Admin mengekspor pesan masuk ke CSV (' . count($inquiries) . ' baris)');
            exit;
        }

        // Summary counts
        $countsStmt = $db->query("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'unread' THEN 1 ELSE 0 END) AS unread,
                SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) AS `read`,
                SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) AS contacted,
                SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) AS archived
            FROM contact_inquiries
        ");
        $counts = $countsStmt->fetch();

        sendJsonResponse([
            'counts' => [
                'total' => (int)($counts['total'] ?? 0),
                'unread' => (int)($counts['unread'] ?? 0),
                'read' => (int)($counts['read'] ?? 0),
                'contacted' => (int)($counts['contacted'] ?? 0),
                'archived' => (int)($counts['archived'] ?? 0)
            ],
            'items' => $inquiries
        ]);
    }

    // 2. PUT: Update status pesan (misal ditandai telah dihubungi / dibaca)
    if ($method === 'PUT') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        $newStatus = trim($input['status'] ?? '');

        if (!$id || !in_array($newStatus, ['unread', 'read', 'contacted', 'archived'])) {
            sendJsonError('ID pesan atau status tidak valid.');
        }

        $stmt = $db->prepare("UPDATE contact_inquiries SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);

        logAdminActivity('update', 'contact_inquiries', (string)$id, "Admin mengubah status pesan #{$id} menjadi {$newStatus}");
        sendJsonResponse(['id' => $id, 'status' => $newStatus], 200, 'Status pesan berhasil diperbarui.');
    }

    // 3. DELETE: Hapus pesan masuk
    if ($method === 'DELETE') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID pesan tidak valid.');
        }

        $stmt = $db->prepare("DELETE FROM contact_inquiries WHERE id = ?");
        $stmt->execute([$id]);

        logAdminActivity('delete', 'contact_inquiries', (string)$id, "Admin menghapus pesan masuk #{$id}");
        sendJsonResponse(null, 200, 'Pesan berhasil dihapus.');
    }

    sendJsonError('Metode HTTP tidak didukung.', 405);

} catch (Throwable $e) {
    sendJsonError('Operasi gagal: ' . $e->getMessage(), 500);
}
