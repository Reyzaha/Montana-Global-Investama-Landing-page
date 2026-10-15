<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN NEWSLETTER SUBSCRIBERS
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

    // 1. GET: Ambil daftar subscriber atau ekspor CSV
    if ($method === 'GET') {
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $export = isset($_GET['export']) ? trim($_GET['export']) : '';

        $sql = "SELECT * FROM newsletter_subscribers WHERE 1=1";
        $params = [];

        if ($status !== '' && in_array($status, ['active', 'unsubscribed'])) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        if ($search !== '') {
            $sql .= " AND email LIKE ?";
            $params[] = '%' . $search . '%';
        }

        $sql .= " ORDER BY subscribed_at DESC LIMIT 2000";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $subscribers = $stmt->fetchAll();

        // Ekspor ke CSV
        if ($export === 'csv') {
            while (ob_get_level() > 0) ob_end_clean();
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="subscriber-newsletter-mgi-' . date('Ymd-His') . '.csv"');
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($out, ['ID', 'Email', 'Status', 'Halaman Sumber', 'IP Address', 'Tanggal Berlangganan']);
            foreach ($subscribers as $row) {
                fputcsv($out, [
                    $row['id'],
                    $row['email'],
                    $row['status'],
                    $row['source_page'],
                    $row['ip_address'],
                    $row['subscribed_at']
                ]);
            }
            fclose($out);
            logAdminActivity('export_subscribers', 'newsletter_subscribers', null, 'Admin mengekspor data subscriber ke CSV (' . count($subscribers) . ' email)');
            exit;
        }

        // Statistik ringkas
        $countsStmt = $db->query("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status = 'unsubscribed' THEN 1 ELSE 0 END) AS unsubscribed
            FROM newsletter_subscribers
        ");
        $counts = $countsStmt->fetch();

        sendJsonResponse([
            'counts' => [
                'total' => (int)($counts['total'] ?? 0),
                'active' => (int)($counts['active'] ?? 0),
                'unsubscribed' => (int)($counts['unsubscribed'] ?? 0)
            ],
            'items' => $subscribers
        ]);
    }

    // 2. PUT: Toggle status active / unsubscribed
    if ($method === 'PUT') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID subscriber tidak valid.');
        }

        $stmt = $db->prepare("SELECT status FROM newsletter_subscribers WHERE id = ?");
        $stmt->execute([$id]);
        $currentStatus = $stmt->fetchColumn();

        $newStatus = ($currentStatus === 'active') ? 'unsubscribed' : 'active';
        $stmt = $db->prepare("UPDATE newsletter_subscribers SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);

        logAdminActivity('update', 'newsletter_subscribers', (string)$id, "Admin mengubah status subscriber #{$id} menjadi {$newStatus}");
        sendJsonResponse(['id' => $id, 'status' => $newStatus], 200, "Status langganan berhasil diubah menjadi {$newStatus}.");
    }

    // 3. DELETE: Hapus subscriber
    if ($method === 'DELETE') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID subscriber tidak valid.');
        }

        $stmt = $db->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
        $stmt->execute([$id]);

        logAdminActivity('delete', 'newsletter_subscribers', (string)$id, "Admin menghapus subscriber #{$id}");
        sendJsonResponse(null, 200, 'Subscriber berhasil dihapus.');
    }

    sendJsonError('Metode HTTP tidak didukung.', 405);

} catch (Throwable $e) {
    sendJsonError('Operasi gagal: ' . $e->getMessage(), 500);
}
