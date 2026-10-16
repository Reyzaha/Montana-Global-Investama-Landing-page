<?php
/**
 * Database & JSON Image Updater
 * Maps the 11 projects to the newly uploaded scale-based drone photos (assets/img/1.png - 9.png)
 * Also updates project_updates and data/projects.json
 */

require_once __DIR__ . '/../backend/config/db.php';

echo "=== MGI PROJECT IMAGE SYNC & MIGRATION ===\n\n";

$mapping = [
    'proj-jkt-jabar'   => 'assets/img/1.png', // Rp 15 M (126 unit) Mega Yard Jabodetabek & Tol/Flyover
    'proj-kebumen'     => 'assets/img/6.png', // Rp 15 M (42 unit) Central Workshop PT MIU & Pabrik Perakitan
    'proj-aceh'        => 'assets/img/3.png', // Rp 7.5 M (21 unit) Kubah Masjid & Perbukitan Tropis Aceh
    'proj-sumatra'     => 'assets/img/3.png', // Rp 7 M (20 unit) Perbukitan Tropis & Unit Sumatera
    'proj-surabaya'    => 'assets/img/7.png', // Rp 5 M (14 unit) Depot Berpagar Beton Industri Surabaya Jatim
    'proj-denpasar'    => 'assets/img/9.png', // Rp 5 M (14 unit) Nuansa Gapura Khas Bali di Kaki Gunung
    'proj-ntt'         => 'assets/img/4.png', // Rp 5 M (14 unit) Savana Kering & Teluk Laut NTT
    'proj-banjarmasin' => 'assets/img/8.png', // Rp 3 M (8 unit) Kanal Air/Sungai & Kayu Kalsel
    'proj-makassar'    => 'assets/img/5.png', // Rp 3 M (8 unit) Padang Pesisir Terbuka & Palem Sulsel
    'proj-pontianak'   => 'assets/img/2.png', // Rp 2 M (6 unit) Tepi Sungai Besar & Dermaga Pontianak Kapuas
    'proj-palu'        => 'assets/img/4.png', // Rp 2 M (6 unit) Pesisir Teluk Palu / Perbukitan
];

try {
    $db = getDB();
    $updateStmt = $db->prepare("UPDATE projects SET image = :image WHERE id = :id");

    echo "1. Memperbarui tabel 'projects' di database:\n";
    $updatedCount = 0;
    foreach ($mapping as $projectId => $newImage) {
        $updateStmt->execute([
            ':image' => $newImage,
            ':id'    => $projectId
        ]);
        if ($updateStmt->rowCount() > 0) {
            echo "   [UPDATED] $projectId -> $newImage\n";
            $updatedCount++;
        } else {
            // Check if it already has this image
            $checkStmt = $db->prepare("SELECT image FROM projects WHERE id = :id");
            $checkStmt->execute([':id' => $projectId]);
            $currentImg = $checkStmt->fetchColumn();
            echo "   [CURRENT] $projectId -> $currentImg\n";
        }
    }
    echo "   Total projects diproses: " . count($mapping) . "\n\n";

    // 2. Update campaign_updates that reference old deleted project-jabodetabek.jpg
    echo "2. Memperbarui tabel 'campaign_updates' (jika ada):\n";
    $tableCheck = $db->query("SHOW TABLES LIKE 'campaign_updates'")->fetchColumn();
    if ($tableCheck) {
        $cuStmt = $db->prepare("UPDATE campaign_updates SET image_url = 'assets/img/1.png' WHERE image_url LIKE '%project-jabodetabek%' OR image_url LIKE '%project jabodetabek%'");
        $cuStmt->execute();
        echo "   Row campaign_updates terupdate: " . $cuStmt->rowCount() . "\n\n";
    } else {
        echo "   Tabel 'campaign_updates' tidak ditemukan, dilewati.\n\n";
    }

} catch (Exception $e) {
    echo "   [ERROR DB] " . $e->getMessage() . "\n\n";
}

// 3. Update data/projects.json
echo "3. Memperbarui file 'data/projects.json':\n";
$jsonPath = __DIR__ . '/../data/projects.json';
if (file_exists($jsonPath)) {
    $raw = file_get_contents($jsonPath);
    $data = json_decode($raw, true);
    if ($data && isset($data['projects']) && is_array($data['projects'])) {
        $jsonUpdated = 0;
        foreach ($data['projects'] as &$p) {
            if (isset($p['id']) && isset($mapping[$p['id']])) {
                $p['image'] = $mapping[$p['id']];
                $jsonUpdated++;
            }
        }
        unset($p);

        file_put_contents($jsonPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        echo "   [SUCCESS] data/projects.json berhasil diperbarui ($jsonUpdated proyek).\n\n";
    } else {
        echo "   [WARN] Gagal parse data/projects.json!\n\n";
    }
} else {
    echo "   [WARN] File data/projects.json tidak ditemukan!\n\n";
}

echo "=== MGI PROJECT IMAGE SYNC COMPLETED ===\n";
