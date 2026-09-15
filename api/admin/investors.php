<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN INVESTORS MANAGEMENT
 * Methods: GET, PUT
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$admin = requireAdminAuth();
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();

    // 1. GET: Ambil daftar investor dengan filter & relasi perusahaan
    if ($method === 'GET') {
        $type = $_GET['type'] ?? null;
        $status = $_GET['status'] ?? null;
        $search = trim($_GET['search'] ?? '');

        $sql = "
            SELECT i.id, i.account_type, i.email, i.full_name, i.citizenship, i.phone,
                   i.status, i.notes, i.created_at, i.updated_at,
                   c.business_name, c.legal_entity, c.company_address, c.pic_name,
                   c.pic_position, c.company_phone, c.annual_turnover
            FROM investors i
            LEFT JOIN investor_companies c ON i.id = c.investor_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($type) && in_array($type, ['perorangan', 'perusahaan'])) {
            $sql .= " AND i.account_type = ?";
            $params[] = $type;
        }

        if (!empty($status) && in_array($status, ['active', 'pending_verification', 'suspended'])) {
            $sql .= " AND i.status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $sql .= " AND (i.email LIKE ? OR i.full_name LIKE ? OR c.business_name LIKE ? OR c.pic_name LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY i.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $investors = $stmt->fetchAll();

        sendJsonResponse($investors);
    }

    // 2. PUT: Update Status Investor atau Catatan
    if ($method === 'PUT') {
        $input = getJsonInput();
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        $status = $input['status'] ?? null;
        $notes = $input['notes'] ?? null;

        if (!$id) {
            sendJsonError('ID Investor wajib disertakan.');
        }

        $stmtCheck = $db->prepare("SELECT id, email, full_name, status FROM investors WHERE id = ?");
        $stmtCheck->execute([$id]);
        $inv = $stmtCheck->fetch();
        if (!$inv) {
            sendJsonError('Investor tidak ditemukan.', 404);
        }

        $allowedStatuses = ['active', 'pending_verification', 'suspended'];
        $newStatus = ($status && in_array($status, $allowedStatuses)) ? $status : $inv['status'];

        $stmtUp = $db->prepare("UPDATE investors SET status = ?, notes = ? WHERE id = ?");
        $stmtUp->execute([$newStatus, $notes, $id]);

        logAdminActivity('update_status', 'investors', (string)$id, "Admin changed investor {$inv['email']} status to {$newStatus}");

        sendJsonResponse([
            'id' => $id,
            'status' => $newStatus
        ], 200, "Status investor {$inv['email']} berhasil diperbarui menjadi '{$newStatus}'.");
    }

    sendJsonError('Method tidak didukung.', 405);

} catch (Exception $e) {
    sendJsonError('Terjadi kesalahan operasi investor: ' . $e->getMessage(), 500);
}
