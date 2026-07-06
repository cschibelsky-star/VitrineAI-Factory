<?php
/**
 * FACTORY_ENTERPRISE_10.0_FULL_PATCH
 * Instalador visual seguro para aplicar a camada Enterprise 10.0.
 * Execute na raiz do projeto Laravel: php factory-enterprise-10/install/apply-enterprise-10.php
 */

$root = realpath(__DIR__ . '/../../');
$patch = $root . '/factory-enterprise-10';
$timestamp = date('Ymd_His');
$backupDir = $root . '/storage/backups/factory_enterprise_10_' . $timestamp;

function ensure_dir(string $path): void {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

function copy_file_safe(string $from, string $to, string $backupDir, string $root): void {
    if (!file_exists($from)) {
        echo "SKIP origem ausente: {$from}\n";
        return;
    }
    if (file_exists($to)) {
        $relative = ltrim(str_replace($root, '', $to), '/');
        $backupPath = $backupDir . '/' . $relative;
        ensure_dir(dirname($backupPath));
        copy($to, $backupPath);
    }
    ensure_dir(dirname($to));
    copy($from, $to);
    echo "OK {$to}\n";
}

ensure_dir($backupDir);

$files = [
    'resources/css/factory-enterprise-10.css' => 'public/factory-enterprise-10/factory-enterprise-10.css',
    'public/factory-enterprise-10/factory-enterprise-10.js' => 'public/factory-enterprise-10/factory-enterprise-10.js',
    'resources/views/layouts/factory-enterprise.blade.php' => 'resources/views/layouts/factory-enterprise.blade.php',
    'resources/views/dashboard-enterprise.blade.php' => 'resources/views/dashboard-enterprise.blade.php',
    'resources/views/comercial/index-enterprise.blade.php' => 'resources/views/comercial/index-enterprise.blade.php',
    'resources/views/clientes/index-enterprise.blade.php' => 'resources/views/clientes/index-enterprise.blade.php',
    'resources/views/factory/studio-enterprise.blade.php' => 'resources/views/factory/studio-enterprise.blade.php',
    'resources/views/marketplace/index-enterprise.blade.php' => 'resources/views/marketplace/index-enterprise.blade.php',
    'resources/views/analytics/index-enterprise.blade.php' => 'resources/views/analytics/index-enterprise.blade.php',
];

foreach ($files as $from => $to) {
    copy_file_safe($patch . '/' . $from, $root . '/' . $to, $backupDir, $root);
}

$artisan = $root . '/artisan';
if (file_exists($artisan)) {
    echo "\nLimpando caches Laravel...\n";
    passthru('php ' . escapeshellarg($artisan) . ' view:clear');
    passthru('php ' . escapeshellarg($artisan) . ' route:clear');
    passthru('php ' . escapeshellarg($artisan) . ' config:clear');
    passthru('php ' . escapeshellarg($artisan) . ' cache:clear');
}

echo "\nFACTORY_ENTERPRISE_10.0_FULL_PATCH aplicado. Backup: {$backupDir}\n";
