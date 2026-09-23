<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN CITIES MANAGEMENT & SEGMENTATION
 * Methods: GET, POST, PUT, DELETE
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$admin = requireAdminAuth();
$method = $_SERVER['REQUEST_METHOD'];
$input = getJsonInput();

// Handle method spoofing from client
if (isset($input['_method'])) {
    $method = strtoupper($input['_method']);
} elseif (isset($_GET['_method'])) {
    $method = strtoupper($_GET['_method']);
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $method = 'DELETE';
}

function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
}

function syncCitiesJson(PDO $db): void {
    try {
        $stmt = $db->query("SELECT id, name, slug, icon, description, is_active_segment, sort_order FROM cities ORDER BY sort_order ASC, name ASC");
        $cities = $stmt->fetchAll();
        $jsonFile = __DIR__ . '/../../data/cities.json';
        file_put_contents($jsonFile, json_encode(['cities' => $cities], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    } catch (Exception $e) {
        error_log("[Sync Cities JSON Error] " . $e->getMessage());
    }
}

function ensureCitiesTable(PDO $db): void {
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS `cities` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `name` VARCHAR(100) NOT NULL,
              `slug` VARCHAR(100) NOT NULL UNIQUE,
              `icon` VARCHAR(50) NOT NULL DEFAULT 'bi-geo-alt-fill',
              `description` VARCHAR(255) NULL,
              `is_active_segment` TINYINT(1) NOT NULL DEFAULT 1,
              `sort_order` INT NOT NULL DEFAULT 0,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Add city_id and city to projects if missing
        $cols = $db->query("SHOW COLUMNS FROM projects LIKE 'city_id'")->fetchAll();
        if (empty($cols)) {
            $db->exec("ALTER TABLE projects ADD COLUMN `city_id` INT NULL AFTER `id`");
            $db->exec("ALTER TABLE projects ADD COLUMN `city` VARCHAR(100) NULL AFTER `city_id`");
        }

        // If empty, seed from data/cities.json
        $count = $db->query("SELECT COUNT(*) FROM cities")->fetchColumn();
        if ((int)$count === 0) {
            $jsonFile = __DIR__ . '/../../data/cities.json';
            if (file_exists($jsonFile)) {
                $raw = json_decode(file_get_contents($jsonFile), true);
                $seedList = $raw['cities'] ?? [];
                $ins = $db->prepare("
                    INSERT INTO cities (name, slug, icon, description, is_active_segment, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                foreach ($seedList as $c) {
                    $ins->execute([
                        $c['name'],
                        $c['slug'] ?? slugify($c['name']),
                        $c['icon'] ?? 'bi-geo-alt-fill',
                        $c['description'] ?? '',
                        isset($c['is_active_segment']) ? (int)$c['is_active_segment'] : 1,
                        (int)($c['sort_order'] ?? 0)
                    ]);
                }
            }
        }

        // Update projects city_id matching
        $db->exec("
            UPDATE projects p
            JOIN cities c ON (LOWER(p.lokasi) LIKE CONCAT('%', LOWER(c.name), '%') OR LOWER(p.title) LIKE CONCAT('%', LOWER(c.name), '%'))
            SET p.city_id = c.id, p.city = c.name
            WHERE p.city_id IS NULL
        ");
    } catch (Exception $e) {
        error_log("[Ensure Cities Table Error] " . $e->getMessage());
    }
}

try {
    $db = getDB();
    ensureCitiesTable($db);

    // 1. GET: Ambil daftar kota atau single kota
    if ($method === 'GET') {
        $id = $_GET['id'] ?? null;

        if ($id) {
            $stmt = $db->prepare("
                SELECT c.*, COUNT(p.id) as project_count
                FROM cities c
                LEFT JOIN projects p ON p.city_id = c.id OR LOWER(p.city) = LOWER(c.name)
                WHERE c.id = ?
                GROUP BY c.id
            ");
            $stmt->execute([$id]);
            $city = $stmt->fetch();

            if (!$city) {
                sendJsonError('Kota tidak ditemukan.', 404);
            }
            sendJsonResponse($city);
        }

        // List all cities with project count
        $stmt = $db->query("
            SELECT c.*, 
                   COUNT(p.id) as project_count,
                   SUM(CASE WHEN p.status = 'Open' THEN 1 ELSE 0 END) as open_project_count
            FROM cities c
            LEFT JOIN projects p ON p.city_id = c.id OR LOWER(p.city) = LOWER(c.name)
            GROUP BY c.id
            ORDER BY c.sort_order ASC, c.name ASC
        ");
        $cities = $stmt->fetchAll();

        sendJsonResponse($cities);
    }

    // 2. TOGGLE SEGMENT (Quick Action)
    if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'toggle_segment') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) {
            sendJsonError('ID Kota tidak valid.');
        }

        $stmt = $db->prepare("SELECT is_active_segment FROM cities WHERE id = ?");
        $stmt->execute([$id]);
        $current = $stmt->fetchColumn();

        if ($current === false) {
            sendJsonError('Kota tidak ditemukan.', 404);
        }

        $newVal = ($current == 1) ? 0 : 1;
        $upd = $db->prepare("UPDATE cities SET is_active_segment = ? WHERE id = ?");
        $upd->execute([$newVal, $id]);

        syncCitiesJson($db);

        sendJsonResponse([
            'message' => 'Status segmen kota berhasil diperbarui.',
            'id' => $id,
            'is_active_segment' => $newVal
        ]);
    }

    // 3. POST: Tambah Kota Baru (Create)
    if ($method === 'POST') {
        $name = trim($input['name'] ?? '');
        if (empty($name)) {
            sendJsonError('Nama kota wajib diisi.');
        }

        $slug = trim($input['slug'] ?? '');
        if (empty($slug)) {
            $slug = slugify($name);
        } else {
            $slug = slugify($slug);
        }

        // Cek keunikan slug atau nama
        $check = $db->prepare("SELECT id FROM cities WHERE slug = ? OR LOWER(name) = LOWER(?)");
        $check->execute([$slug, $name]);
        if ($check->fetch()) {
            sendJsonError("Kota atau slug '{$name}' sudah terdaftar dalam sistem.");
        }

        $icon = trim($input['icon'] ?? 'bi-geo-alt-fill');
        $description = trim($input['description'] ?? '');
        $isActive = isset($input['is_active_segment']) ? (int)$input['is_active_segment'] : 1;
        $sortOrder = (int)($input['sort_order'] ?? 0);

        $stmt = $db->prepare("
            INSERT INTO cities (name, slug, icon, description, is_active_segment, sort_order)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $slug, $icon, $description, $isActive, $sortOrder]);
        $newId = $db->lastInsertId();

        syncCitiesJson($db);

        sendJsonResponse([
            'message' => "Kota '{$name}' berhasil ditambahkan.",
            'id' => $newId
        ], 201);
    }

    // 4. PUT: Update Kota (Update)
    if ($method === 'PUT') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID Kota tidak valid.');
        }

        $stmt = $db->prepare("SELECT * FROM cities WHERE id = ?");
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        if (!$existing) {
            sendJsonError('Kota tidak ditemukan.', 404);
        }

        $name = trim($input['name'] ?? $existing['name']);
        if (empty($name)) {
            sendJsonError('Nama kota tidak boleh kosong.');
        }

        $slug = trim($input['slug'] ?? $existing['slug']);
        if (empty($slug)) {
            $slug = slugify($name);
        } else {
            $slug = slugify($slug);
        }

        // Cek duplikasi slug jika berubah
        if ($slug !== $existing['slug']) {
            $check = $db->prepare("SELECT id FROM cities WHERE slug = ? AND id != ?");
            $check->execute([$slug, $id]);
            if ($check->fetch()) {
                sendJsonError("Slug '{$slug}' sudah digunakan oleh kota lain.");
            }
        }

        $icon = trim($input['icon'] ?? $existing['icon']);
        $description = trim($input['description'] ?? $existing['description']);
        $isActive = isset($input['is_active_segment']) ? (int)$input['is_active_segment'] : (int)$existing['is_active_segment'];
        $sortOrder = isset($input['sort_order']) ? (int)$input['sort_order'] : (int)$existing['sort_order'];

        $upd = $db->prepare("
            UPDATE cities
            SET name = ?, slug = ?, icon = ?, description = ?, is_active_segment = ?, sort_order = ?
            WHERE id = ?
        ");
        $upd->execute([$name, $slug, $icon, $description, $isActive, $sortOrder, $id]);

        // Perbarui juga nama kota di tabel projects yang terhubung
        $updProjects = $db->prepare("UPDATE projects SET city = ? WHERE city_id = ?");
        $updProjects->execute([$name, $id]);

        syncCitiesJson($db);

        sendJsonResponse([
            'message' => "Data kota '{$name}' berhasil diperbarui.",
            'id' => $id
        ]);
    }

    // 5. DELETE: Hapus Kota (Delete)
    if ($method === 'DELETE') {
        $id = (int)($_GET['id'] ?? ($input['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID Kota tidak valid.');
        }

        $stmt = $db->prepare("SELECT name FROM cities WHERE id = ?");
        $stmt->execute([$id]);
        $cityName = $stmt->fetchColumn();
        if (!$cityName) {
            sendJsonError('Kota tidak ditemukan.', 404);
        }

        // Lepas asosiasi dari tabel projects
        $unlink = $db->prepare("UPDATE projects SET city_id = NULL, city = NULL WHERE city_id = ?");
        $unlink->execute([$id]);

        // Hapus kota
        $del = $db->prepare("DELETE FROM cities WHERE id = ?");
        $del->execute([$id]);

        syncCitiesJson($db);

        sendJsonResponse([
            'message' => "Kota '{$cityName}' berhasil dihapus dari sistem."
        ]);
    }

    sendJsonError('Method tidak didukung.', 405);

} catch (Exception $e) {
    sendJsonError('Terjadi kesalahan operasi kota: ' . $e->getMessage(), 500);
}
