<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN CONTENT MANAGEMENT (CMS)
 * Modules: Transformasi Roadmap, Ekosistem Nodes, Preparation Entities & Workflow
 * Method: GET, POST, PUT, DELETE
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$admin = requireAdminAuth();
$section = $_GET['section'] ?? 'transformasi';
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    ensureCmsSchema($db);

    // =========================================================================
    // 1. TRANSFORMASI ROADMAP MILESTONES
    // =========================================================================
    if ($section === 'transformasi') {
        if ($method === 'GET') {
            $stmt = $db->query("SELECT * FROM transformasi_steps ORDER BY step_order ASC");
            $rows = $stmt->fetchAll();

            // Auto-populate from data/transformasi.json if DB table is currently empty
            if (empty($rows)) {
                $jsonFile = __DIR__ . '/../../data/transformasi.json';
                if (file_exists($jsonFile)) {
                    $jsonData = json_decode(file_get_contents($jsonFile), true);
                    if (!empty($jsonData['journey'])) {
                        $insStmt = $db->prepare("
                            INSERT INTO transformasi_steps (step_order, year_or_phase, title, subtitle, description, highlights, status)
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        foreach ($jsonData['journey'] as $j) {
                            $insStmt->execute([
                                (int)($j['step'] ?? 1),
                                $j['year_or_phase'] ?? '',
                                $j['title'] ?? '',
                                $j['tagline'] ?? ($j['subtitle'] ?? ''),
                                $j['description'] ?? '',
                                json_encode($j['achievements'] ?? [], JSON_UNESCAPED_UNICODE),
                                $j['status'] ?? 'completed'
                            ]);
                        }
                        $stmt = $db->query("SELECT * FROM transformasi_steps ORDER BY step_order ASC");
                        $rows = $stmt->fetchAll();
                    }
                }
            }

            sendJsonResponse($rows);
        }

        if ($method === 'POST' || $method === 'PUT') {
            $input = getJsonInput();
            $id = $input['id'] ?? null;
            $stepOrder = (int)($input['step_order'] ?? 1);
            $yearPhase = trim($input['year_or_phase'] ?? '');
            $title = trim($input['title'] ?? '');
            $subtitle = trim($input['subtitle'] ?? '');
            $description = trim($input['description'] ?? '');
            $status = $input['status'] ?? 'completed';

            // Highlights handling
            $highlights = $input['highlights'] ?? [];
            if (is_string($highlights)) {
                $decoded = json_decode($highlights, true);
                $highlights = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode("\n", $highlights)));
            }
            $highlightsJson = json_encode(array_values(array_filter($highlights)), JSON_UNESCAPED_UNICODE);

            if (empty($title)) {
                sendJsonError('Judul milestone transformasi wajib diisi.');
            }

            if ($id) {
                $stmt = $db->prepare("
                    UPDATE transformasi_steps SET
                        step_order = ?, year_or_phase = ?, title = ?, subtitle = ?,
                        description = ?, highlights = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([$stepOrder, $yearPhase, $title, $subtitle, $description, $highlightsJson, $status, $id]);
                logAdminActivity('update', 'transformasi_steps', (string)$id, "Admin updated transformasi step '{$title}'");
            } else {
                $stmt = $db->prepare("
                    INSERT INTO transformasi_steps (step_order, year_or_phase, title, subtitle, description, highlights, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$stepOrder, $yearPhase, $title, $subtitle, $description, $highlightsJson, $status]);
                $id = (int)$db->lastInsertId();
                logAdminActivity('create', 'transformasi_steps', (string)$id, "Admin created transformasi step '{$title}'");
            }

            // Sync to data/transformasi.json
            syncTransformasiJson($db);

            sendJsonResponse(['id' => $id], 200, "Milestone transformasi berhasil disimpan.");
        }

        if ($method === 'DELETE') {
            $id = (int)($_GET['id'] ?? (getJsonInput()['id'] ?? 0));
            if ($id) {
                $db->prepare("DELETE FROM transformasi_steps WHERE id = ?")->execute([$id]);
                logAdminActivity('delete', 'transformasi_steps', (string)$id, "Admin deleted transformasi step ID {$id}");
                syncTransformasiJson($db);
                sendJsonResponse(['id' => $id], 200, "Milestone transformasi berhasil dihapus.");
            }
            sendJsonError('ID milestone tidak valid.');
        }
    }

    // =========================================================================
    // 2. EKOSISTEM KORPORAT NODES
    // =========================================================================
    if ($section === 'ekosistem') {
        if ($method === 'GET') {
            $stmt = $db->query("SELECT * FROM ekosistem_nodes ORDER BY level ASC, sort_order ASC");
            $rows = $stmt->fetchAll();

            $jsonFile = __DIR__ . '/../../data/ekosistem.json';
            $jsonMap = [];
            if (file_exists($jsonFile)) {
                $jsonData = json_decode(file_get_contents($jsonFile), true);
                if (!empty($jsonData['nodes'])) {
                    foreach ($jsonData['nodes'] as $jn) {
                        $jsonMap[$jn['id']] = $jn;
                    }

                    // Auto-populate from data/ekosistem.json if DB table is empty on VPS
                    if (empty($rows)) {
                        $insertStmt = $db->prepare("
                            INSERT IGNORE INTO ekosistem_nodes (
                                id, label, short_label, subtitle, parent_id,
                                level, badge, type, category, icon,
                                role_desc, sort_order
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $order = 1;
                        foreach ($jsonData['nodes'] as $node) {
                            $insertStmt->execute([
                                $node['id'],
                                $node['label'],
                                $node['short_label'] ?? $node['label'],
                                $node['subtitle'] ?? null,
                                $node['parent'] ?? null,
                                (int)($node['level'] ?? 0),
                                $node['badge'] ?? null,
                                $node['type'] ?? 'subsidiary',
                                $node['category'] ?? null,
                                $node['icon'] ?? 'building',
                                $node['description'] ?? null,
                                $order++
                            ]);
                        }
                        $stmt = $db->query("SELECT * FROM ekosistem_nodes ORDER BY level ASC, sort_order ASC");
                        $rows = $stmt->fetchAll();
                    }
                }
            }

            // Sanitize rows with fallback values
            $sanitized = [];
            foreach ($rows as $r) {
                $id = $r['id'];
                $fb = $jsonMap[$id] ?? [];
                $r['short_label'] = !empty($r['short_label']) ? $r['short_label'] : ($fb['short_label'] ?? $r['label']);
                $r['type'] = !empty($r['type']) ? $r['type'] : ($fb['type'] ?? ($r['level'] == 0 ? 'holding' : ($r['level'] == 2 ? 'facility' : 'subsidiary')));
                $r['category'] = !empty($r['category']) ? $r['category'] : ($fb['category'] ?? '');
                $r['icon'] = !empty($r['icon']) ? $r['icon'] : ($fb['icon'] ?? 'building');
                $r['badge'] = !empty($r['badge']) ? $r['badge'] : ($fb['badge'] ?? '');
                $r['role_desc'] = !empty($r['role_desc']) ? $r['role_desc'] : ($fb['description'] ?? '');
                $sanitized[] = $r;
            }

            sendJsonResponse($sanitized);
        }

        if ($method === 'POST' || $method === 'PUT') {
            $input = getJsonInput();
            $id = trim($input['id'] ?? '');
            $label = trim($input['label'] ?? '');
            $shortLabel = trim($input['short_label'] ?? ($input['shortLabel'] ?? $label));
            $subtitle = trim($input['subtitle'] ?? '');
            $parentId = !empty($input['parent_id']) ? trim($input['parent_id']) : (!empty($input['parent']) ? trim($input['parent']) : null);
            $level = (int)($input['level'] ?? 0);
            $badge = trim($input['badge'] ?? '');
            $type = trim($input['type'] ?? 'subsidiary');
            $category = trim($input['category'] ?? '');
            $icon = trim($input['icon'] ?? 'building');
            $roleDesc = trim($input['role_desc'] ?? ($input['description'] ?? ''));
            $sortOrder = (int)($input['sort_order'] ?? 0);
            $isEditing = !empty($input['is_edit']);

            if (empty($id) || empty($label)) {
                sendJsonError('ID Node dan Nama Entitas / Label wajib diisi.');
            }

            // Check if existing
            $stmtEx = $db->prepare("SELECT id FROM ekosistem_nodes WHERE id = ?");
            $stmtEx->execute([$id]);
            $exists = $stmtEx->fetch();

            if ($exists && $isEditing) {
                $stmt = $db->prepare("
                    UPDATE ekosistem_nodes SET
                        label = ?, short_label = ?, subtitle = ?, parent_id = ?,
                        level = ?, badge = ?, type = ?, category = ?, icon = ?,
                        role_desc = ?, sort_order = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $label, $shortLabel, $subtitle, $parentId,
                    $level, $badge, $type, $category, $icon,
                    $roleDesc, $sortOrder, $id
                ]);
                logAdminActivity('update', 'ekosistem_nodes', $id, "Admin updated ekosistem node '{$label}'");
            } else if (!$exists) {
                $stmt = $db->prepare("
                    INSERT INTO ekosistem_nodes (
                        id, label, short_label, subtitle, parent_id,
                        level, badge, type, category, icon,
                        role_desc, sort_order
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $id, $label, $shortLabel, $subtitle, $parentId,
                    $level, $badge, $type, $category, $icon,
                    $roleDesc, $sortOrder
                ]);
                logAdminActivity('create', 'ekosistem_nodes', $id, "Admin created ekosistem node '{$label}'");
            } else {
                sendJsonError("ID Node '{$id}' sudah terdaftar. Silakan gunakan ID lain atau edit node yang ada.");
            }

            // Sync to data/ekosistem.json
            syncEkosistemJson($db);

            sendJsonResponse(['id' => $id], 200, "Node ekosistem '{$label}' berhasil disimpan.");
        }

        if ($method === 'DELETE') {
            $id = trim($_GET['id'] ?? (getJsonInput()['id'] ?? ''));
            if (!empty($id)) {
                $stmtTarget = $db->prepare("SELECT label FROM ekosistem_nodes WHERE id = ?");
                $stmtTarget->execute([$id]);
                $target = $stmtTarget->fetch();

                $db->prepare("DELETE FROM ekosistem_nodes WHERE id = ?")->execute([$id]);
                logAdminActivity('delete', 'ekosistem_nodes', $id, "Admin deleted ekosistem node ID '{$id}'");
                syncEkosistemJson($db);
                sendJsonResponse(['id' => $id], 200, "Node ekosistem berhasil dihapus.");
            }
            sendJsonError('ID Node tidak valid.');
        }
    }

    // =========================================================================
    // 3. PREPARATION ENTITIES & WORKFLOW
    // =========================================================================
    if ($section === 'preparation') {
        if ($method === 'GET') {
            $stmtEnt = $db->query("SELECT * FROM preparation_entities ORDER BY sort_order ASC");
            $entities = $stmtEnt->fetchAll();

            $stmtWf = $db->query("SELECT * FROM preparation_workflow ORDER BY step_number ASC");
            $workflow = $stmtWf->fetchAll();

            // Auto-populate from data/preparation.json if DB tables are empty
            $jsonFile = __DIR__ . '/../../data/preparation.json';
            if (file_exists($jsonFile)) {
                $prepData = json_decode(file_get_contents($jsonFile), true);

                if (empty($entities) && !empty($prepData['entities_summary'])) {
                    $insEnt = $db->prepare("
                        INSERT IGNORE INTO preparation_entities (
                            code, name, slogan, role, level, badge, color, description, focus, output, location, sort_order
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $order = 1;
                    foreach ($prepData['entities_summary'] as $e) {
                        $insEnt->execute([
                            $e['code'],
                            $e['name'],
                            $e['slogan'] ?? '',
                            $e['role'] ?? '',
                            $e['level'] ?? 'Level 1',
                            $e['badge'] ?? $e['code'],
                            $e['color'] ?? 'gold',
                            $e['description'] ?? '',
                            $e['fokus_kerja'] ?? ($e['focus'] ?? ''),
                            $e['output_utama'] ?? ($e['output'] ?? ''),
                            $e['location'] ?? 'Indonesia',
                            $order++
                        ]);
                    }
                    $stmtEnt = $db->query("SELECT * FROM preparation_entities ORDER BY sort_order ASC");
                    $entities = $stmtEnt->fetchAll();
                }

                if (empty($workflow) && !empty($prepData['workflow'])) {
                    $insWf = $db->prepare("
                        INSERT INTO preparation_workflow (step_number, title, actor, description, badge)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    foreach ($prepData['workflow'] as $w) {
                        $insWf->execute([
                            (int)($w['step'] ?? 1),
                            $w['title'] ?? '',
                            $w['actor'] ?? '',
                            $w['desc'] ?? ($w['description'] ?? ''),
                            "Tahap " . ($w['step'] ?? 1)
                        ]);
                    }
                    $stmtWf = $db->query("SELECT * FROM preparation_workflow ORDER BY step_number ASC");
                    $workflow = $stmtWf->fetchAll();
                }
            }

            sendJsonResponse([
                'entities' => $entities,
                'workflow' => $workflow
            ]);
        }

        // Sub-action: ENTITY UPDATE
        if (($method === 'POST' || $method === 'PUT') && ($_GET['type'] ?? '') === 'entity') {
            $input = getJsonInput();
            $code = trim($input['code'] ?? '');
            $name = trim($input['name'] ?? '');
            $slogan = trim($input['slogan'] ?? '');
            $role = trim($input['role'] ?? '');
            $level = trim($input['level'] ?? 'Level 1');
            $badge = trim($input['badge'] ?? $code);
            $color = trim($input['color'] ?? 'gold');
            $description = trim($input['description'] ?? '');
            $focus = trim($input['focus'] ?? ($input['fokus_kerja'] ?? ''));
            $output = trim($input['output'] ?? ($input['output_utama'] ?? ''));
            $location = trim($input['location'] ?? 'Indonesia');

            if (empty($code) || empty($name)) {
                sendJsonError('Kode dan Nama Entitas wajib diisi.');
            }

            $stmt = $db->prepare("
                UPDATE preparation_entities SET
                    name = ?, slogan = ?, role = ?, level = ?, badge = ?,
                    color = ?, description = ?, focus = ?, output = ?, location = ?
                WHERE code = ?
            ");
            $stmt->execute([$name, $slogan, $role, $level, $badge, $color, $description, $focus, $output, $location, $code]);

            logAdminActivity('update', 'preparation_entities', $code, "Admin updated preparation entity '{$name}'");
            syncPreparationJson($db);

            sendJsonResponse(['code' => $code], 200, "Profil entitas '{$name}' berhasil diperbarui.");
        }

        // Sub-action: WORKFLOW CREATE / UPDATE
        if (($method === 'POST' || $method === 'PUT') && (($_GET['type'] ?? '') === 'workflow' || empty($_GET['type']))) {
            $input = getJsonInput();
            $id = $input['id'] ?? null;
            $stepNumber = (int)($input['step_number'] ?? ($input['step'] ?? 1));
            $title = trim($input['title'] ?? '');
            $actor = trim($input['actor'] ?? '');
            $desc = trim($input['description'] ?? ($input['desc'] ?? ''));
            $badge = trim($input['badge'] ?? "Tahap {$stepNumber}");

            if (empty($title) || empty($actor)) {
                sendJsonError('Judul Tahapan dan Pihak / Aktor wajib diisi.');
            }

            if ($id) {
                $stmt = $db->prepare("
                    UPDATE preparation_workflow SET
                        step_number = ?, title = ?, actor = ?, description = ?, badge = ?
                    WHERE id = ?
                ");
                $stmt->execute([$stepNumber, $title, $actor, $desc, $badge, $id]);
                logAdminActivity('update', 'preparation_workflow', (string)$id, "Admin updated preparation workflow step {$stepNumber}");
            } else {
                $stmt = $db->prepare("
                    INSERT INTO preparation_workflow (step_number, title, actor, description, badge)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$stepNumber, $title, $actor, $desc, $badge]);
                $id = (int)$db->lastInsertId();
                logAdminActivity('create', 'preparation_workflow', (string)$id, "Admin created preparation workflow step {$stepNumber}");
            }

            syncPreparationJson($db);
            sendJsonResponse(['id' => $id], 200, "Tahapan alur kerja preparation berhasil disimpan.");
        }

        if ($method === 'DELETE' && ($_GET['type'] ?? '') === 'workflow') {
            $id = (int)($_GET['id'] ?? (getJsonInput()['id'] ?? 0));
            if ($id) {
                $db->prepare("DELETE FROM preparation_workflow WHERE id = ?")->execute([$id]);
                logAdminActivity('delete', 'preparation_workflow', (string)$id, "Admin deleted preparation workflow step ID {$id}");
                syncPreparationJson($db);
                sendJsonResponse(['id' => $id], 200, "Tahapan workflow berhasil dihapus.");
            }
            sendJsonError('ID tahapan tidak valid.');
        }
    }

    // =========================================================================
    // 4. COMPANY PROFILE & TATA KELOLA (TARIF)
    // =========================================================================
    if ($section === 'profile') {
        $jsonFile = __DIR__ . '/../../data/company-profile.json';

        if ($method === 'GET') {
            if (file_exists($jsonFile)) {
                $data = json_decode(file_get_contents($jsonFile), true);
                sendJsonResponse($data);
            }

            // Fallback from DB
            $stmt = $db->prepare("SELECT setting_value FROM company_profile WHERE setting_key = 'main_profile' LIMIT 1");
            $stmt->execute();
            $row = $stmt->fetch();
            if ($row && !empty($row['setting_value'])) {
                sendJsonResponse(json_decode($row['setting_value'], true));
            }

            sendJsonError('Data profil perusahaan tidak ditemukan.', 404);
        }

        if ($method === 'POST' || $method === 'PUT') {
            $input = getJsonInput();
            if (empty($input)) {
                sendJsonError('Data profil perusahaan kosong.');
            }

            $jsonString = json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            // 1. Save to JSON file
            file_put_contents($jsonFile, $jsonString);

            // 2. Save to MySQL table
            try {
                $syncStmt = $db->prepare("
                    INSERT INTO company_profile (setting_key, setting_value)
                    VALUES ('main_profile', :val)
                    ON DUPLICATE KEY UPDATE setting_value = :val2
                ");
                $syncStmt->execute([':val' => $jsonString, ':val2' => $jsonString]);
            } catch (Exception $dbEx) {}

            logAdminActivity('update', 'company_profile', 'main_profile', 'Admin memperbarui profil perusahaan & tata kelola');

            sendJsonResponse($input, 200, 'Profil perusahaan dan tata kelola berhasil disimpan.');
        }
    }

    sendJsonError("Section CMS '{$section}' tidak valid.", 400);

} catch (Exception $e) {
    sendJsonException($e, 'Terjadi kesalahan sistem pada CMS Konten.');
}

// =============================================================================
// JSON SYNC HELPER FUNCTIONS (Ensures instant fallback sync)
// =========================================================================

function syncTransformasiJson(PDO $db): void {
    try {
        $stmt = $db->query("SELECT * FROM transformasi_steps ORDER BY step_order ASC");
        $steps = $stmt->fetchAll();
        $journey = [];
        foreach ($steps as $st) {
            $achievements = !empty($st['highlights']) ? json_decode($st['highlights'], true) : [];
            $journey[] = [
                'step' => (int)$st['step_order'],
                'year_or_phase' => $st['year_or_phase'],
                'badge' => "Milestone " . $st['step_order'],
                'title' => $st['title'],
                'tagline' => $st['subtitle'],
                'description' => $st['description'],
                'achievements' => $achievements ?: [],
                'status' => $st['status']
            ];
        }

        $jsonFile = __DIR__ . '/../../data/transformasi.json';
        $existing = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
        $existing['journey'] = $journey;
        file_put_contents($jsonFile, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    } catch (Exception $e) {
        error_log("[CMS Sync Transformasi Error] " . $e->getMessage());
    }
}

function syncEkosistemJson(PDO $db): void {
    try {
        $stmt = $db->query("SELECT * FROM ekosistem_nodes ORDER BY level ASC, sort_order ASC");
        $rawNodes = $stmt->fetchAll();
        $nodes = [];
        foreach ($rawNodes as $n) {
            $nodes[] = [
                'id' => $n['id'],
                'label' => $n['label'],
                'short_label' => $n['short_label'] ?? $n['label'],
                'subtitle' => $n['subtitle'] ?? '',
                'type' => $n['type'] ?? 'subsidiary',
                'category' => $n['category'] ?? '',
                'parent' => $n['parent_id'],
                'level' => (int)$n['level'],
                'icon' => $n['icon'] ?? 'building',
                'badge' => $n['badge'] ?? '',
                'description' => $n['role_desc'] ?? ''
            ];
        }

        $jsonFile = __DIR__ . '/../../data/ekosistem.json';
        $existing = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
        $existing['nodes'] = $nodes;
        file_put_contents($jsonFile, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    } catch (Exception $e) {
        error_log("[CMS Sync Ekosistem Error] " . $e->getMessage());
    }
}

function syncPreparationJson(PDO $db): void {
    try {
        $stmtEnt = $db->query("SELECT * FROM preparation_entities ORDER BY sort_order ASC");
        $rawEnt = $stmtEnt->fetchAll();
        $entities = [];
        foreach ($rawEnt as $e) {
            $icon = match($e['code']) {
                'MGI' => 'building',
                'MIU' => 'truck',
                'MSI' => 'gear-wide-connected',
                'Mypurcase' => 'cart-check',
                default => 'briefcase'
            };
            $entities[] = [
                'code' => $e['code'],
                'name' => $e['name'],
                'role' => $e['role'],
                'fokus_kerja' => $e['focus'],
                'output_utama' => $e['output'],
                'color' => $e['color'] ?? 'gold',
                'icon' => $icon
            ];
        }

        $stmtWf = $db->query("SELECT * FROM preparation_workflow ORDER BY step_number ASC");
        $rawWf = $stmtWf->fetchAll();
        $workflow = [];
        foreach ($rawWf as $w) {
            $workflow[] = [
                'step' => (int)$w['step_number'],
                'title' => $w['title'],
                'actor' => $w['actor'],
                'desc' => $w['description']
            ];
        }

        $jsonFile = __DIR__ . '/../../data/preparation.json';
        $existing = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
        $existing['entities_summary'] = $entities;
        $existing['workflow'] = $workflow;
        file_put_contents($jsonFile, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    } catch (Exception $e) {
        error_log("[CMS Sync Preparation Error] " . $e->getMessage());
    }
}

/**
 * Self-healing automatic schema migration for CMS tables & columns
 */
function ensureCmsSchema(PDO $db): void {
    // 1. Check ekosistem_nodes table and columns
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS `ekosistem_nodes` (
              `id` VARCHAR(50) PRIMARY KEY,
              `label` VARCHAR(255) NOT NULL,
              `short_label` VARCHAR(150) NULL,
              `subtitle` VARCHAR(255) NULL,
              `parent_id` VARCHAR(50) NULL,
              `level` INT NOT NULL DEFAULT 0,
              `badge` VARCHAR(100) NULL,
              `type` VARCHAR(50) NULL,
              `category` VARCHAR(150) NULL,
              `icon` VARCHAR(50) NULL DEFAULT 'building',
              `role_desc` TEXT NULL,
              `sort_order` INT NOT NULL DEFAULT 0,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $cols = $db->query("DESCRIBE ekosistem_nodes")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('short_label', $cols)) {
            $db->exec("ALTER TABLE ekosistem_nodes ADD COLUMN `short_label` VARCHAR(150) NULL AFTER `label`");
        }
        if (!in_array('type', $cols)) {
            $db->exec("ALTER TABLE ekosistem_nodes ADD COLUMN `type` VARCHAR(50) NULL AFTER `badge`");
        }
        if (!in_array('category', $cols)) {
            $db->exec("ALTER TABLE ekosistem_nodes ADD COLUMN `category` VARCHAR(150) NULL AFTER `type`");
        }
        if (!in_array('icon', $cols)) {
            $db->exec("ALTER TABLE ekosistem_nodes ADD COLUMN `icon` VARCHAR(50) NULL DEFAULT 'building' AFTER `category`");
        }
        if (!in_array('role_desc', $cols)) {
            $db->exec("ALTER TABLE ekosistem_nodes ADD COLUMN `role_desc` TEXT NULL AFTER `icon`");
        }
    } catch (Exception $e) {
        error_log("[CMS Schema Migration ekosistem_nodes Error] " . $e->getMessage());
    }

    // 2. Ensure transformasi_steps
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS `transformasi_steps` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `step_order` INT NOT NULL DEFAULT 1,
              `year_or_phase` VARCHAR(50) NOT NULL,
              `title` VARCHAR(255) NOT NULL,
              `subtitle` VARCHAR(255) NULL,
              `description` TEXT NOT NULL,
              `highlights` JSON NULL,
              `status` VARCHAR(50) NOT NULL DEFAULT 'completed',
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    } catch (Exception $e) {
        error_log("[CMS Schema Migration transformasi_steps Error] " . $e->getMessage());
    }

    // 3. Ensure preparation_entities
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS `preparation_entities` (
              `code` VARCHAR(50) PRIMARY KEY,
              `name` VARCHAR(255) NOT NULL,
              `slogan` VARCHAR(255) NULL,
              `role` VARCHAR(255) NOT NULL,
              `level` VARCHAR(100) NOT NULL,
              `badge` VARCHAR(100) NOT NULL,
              `color` VARCHAR(50) NOT NULL DEFAULT 'gold',
              `description` TEXT NOT NULL,
              `focus` TEXT NOT NULL,
              `output` TEXT NOT NULL,
              `location` VARCHAR(255) NULL,
              `sort_order` INT NOT NULL DEFAULT 0,
              `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    } catch (Exception $e) {
        error_log("[CMS Schema Migration preparation_entities Error] " . $e->getMessage());
    }

    // 4. Ensure preparation_workflow
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS `preparation_workflow` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `step_number` INT NOT NULL DEFAULT 1,
              `title` VARCHAR(255) NOT NULL,
              `actor` VARCHAR(255) NOT NULL,
              `description` TEXT NOT NULL,
              `badge` VARCHAR(100) NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    } catch (Exception $e) {
        error_log("[CMS Schema Migration preparation_workflow Error] " . $e->getMessage());
    }
}

