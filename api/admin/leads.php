<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN LEADS (CRM FOLLOW-UP)
 * GET              → daftar lead + statistik pipeline (filter: status, search, from, to)
 * GET ?export=csv  → unduh CSV
 * PUT              → perbarui status / catatan / PIC
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';
require_once __DIR__ . '/../../backend/helpers/lead_helper.php';

$admin = requireAdminAuth();
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();

    if ($method === 'GET') {
        $status = $_GET['status'] ?? '';
        $search = trim($_GET['search'] ?? '');
        $from   = $_GET['from'] ?? '';
        $to     = $_GET['to'] ?? '';

        $where = ' WHERE 1=1';
        $params = [];

        if ($status !== '' && array_key_exists($status, LEAD_STATUSES)) {
            $where .= ' AND status = ?';
            $params[] = $status;
        }
        if ($search !== '') {
            $where .= ' AND (full_name LIKE ? OR phone LIKE ? OR city LIKE ? OR utm_campaign LIKE ? OR utm_term LIKE ?)';
            $term = '%' . $search . '%';
            array_push($params, $term, $term, $term, $term, $term);
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $where .= ' AND created_at >= ?';
            $params[] = $from . ' 00:00:00';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $where .= ' AND created_at <= ?';
            $params[] = $to . ' 23:59:59';
        }

        $stmt = $db->prepare('SELECT * FROM leads' . $where . ' ORDER BY created_at DESC LIMIT 1000');
        $stmt->execute($params);
        $leads = $stmt->fetchAll();

        // CSV export
        if (($_GET['export'] ?? '') === 'csv') {
            while (ob_get_level() > 0) ob_end_clean();
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="leads-mgi-' . date('Ymd-His') . '.csv"');
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            fputcsv($out, ['ID', 'Tanggal', 'Nama', 'WhatsApp', 'Kota', 'Rencana Nominal', 'Status', 'PIC', 'Catatan',
                'UTM Source', 'UTM Medium', 'UTM Campaign', 'UTM Term', 'GCLID', 'Landing Page']);
            foreach ($leads as $l) {
                fputcsv($out, [
                    $l['id'], $l['created_at'], $l['full_name'], '+' . $l['phone'], $l['city'], $l['investment_range'],
                    LEAD_STATUSES[$l['status']] ?? $l['status'], $l['assigned_to'], $l['admin_notes'],
                    $l['utm_source'], $l['utm_medium'], $l['utm_campaign'], $l['utm_term'], $l['gclid'], $l['landing_page'],
                ]);
            }
            fclose($out);
            logAdminActivity('export_leads', 'leads', null, 'Admin exported ' . count($leads) . ' leads to CSV');
            exit;
        }

        // Statistik pipeline (seluruh data, tanpa filter)
        $stats = array_fill_keys(array_keys(LEAD_STATUSES), 0);
        foreach ($db->query('SELECT status, COUNT(*) AS c FROM leads GROUP BY status')->fetchAll() as $row) {
            $stats[$row['status']] = (int)$row['c'];
        }
        $today = (int)$db->query('SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()')->fetchColumn();
        $week  = (int)$db->query('SELECT COUNT(*) FROM leads WHERE created_at >= (NOW() - INTERVAL 7 DAY)')->fetchColumn();

        sendJsonResponse([
            'leads'    => $leads,
            'stats'    => $stats,
            'today'    => $today,
            'week'     => $week,
            'statuses' => LEAD_STATUSES,
        ]);
    }

    if ($method === 'PUT') {
        $input = getJsonInput();
        $id = (int)($input['id'] ?? 0);
        if (!$id) sendJsonError('ID lead wajib disertakan.');

        $stmt = $db->prepare('SELECT id, full_name, status, contacted_at FROM leads WHERE id = ?');
        $stmt->execute([$id]);
        $lead = $stmt->fetch();
        if (!$lead) sendJsonError('Lead tidak ditemukan.', 404);

        $newStatus = $input['status'] ?? $lead['status'];
        if (!array_key_exists($newStatus, LEAD_STATUSES)) sendJsonError('Status tidak valid.');

        $fields = ['status = ?'];
        $params = [$newStatus];

        if (array_key_exists('admin_notes', $input)) {
            $fields[] = 'admin_notes = ?';
            $params[] = cleanLeadText((string)$input['admin_notes'], 5000);
        }
        if (array_key_exists('assigned_to', $input)) {
            $fields[] = 'assigned_to = ?';
            $params[] = cleanLeadText((string)$input['assigned_to'], 100);
        }
        // Catat waktu pertama kali dihubungi (untuk mengukur kecepatan respons)
        if ($newStatus !== 'new' && empty($lead['contacted_at'])) {
            $fields[] = 'contacted_at = NOW()';
        }

        $params[] = $id;
        $db->prepare('UPDATE leads SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);

        logAdminActivity('update_lead', 'leads', (string)$id, "Lead {$lead['full_name']} → {$newStatus}");

        sendJsonResponse(['id' => $id, 'status' => $newStatus], 200, 'Lead berhasil diperbarui.');
    }

    sendJsonError('Method tidak didukung.', 405);

} catch (Throwable $e) {
    sendJsonException($e, 'Terjadi kesalahan saat memproses data lead.');
}
