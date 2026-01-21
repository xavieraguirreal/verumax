<?php
/**
 * VERUMAX SUPER ADMIN - Header
 */

if (!defined('VERUMAX_ADMIN_PATH')) {
    die('Acceso denegado');
}

require_auth();

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Dashboard') ?> - <?= e(VERUMAX_ADMIN_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <?php include VERUMAX_ADMIN_PATH . '/includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 ml-64">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-xl font-semibold text-gray-800"><?= e($page_title ?? 'Dashboard') ?></h1>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600">
                            <?= e($_SESSION['superadmin_nombre'] ?? 'Usuario') ?>
                        </span>
                        <a href="logout.php" class="text-sm text-red-600 hover:text-red-800 transition">
                            Cerrar sesión
                        </a>
                    </div>
                </div>
            </header>

            <!-- Flash Messages -->
            <?php $flashes = get_flash(); ?>
            <?php if (!empty($flashes)): ?>
            <div class="px-6 pt-4">
                <?php foreach ($flashes as $flash): ?>
                <div class="mb-2 px-4 py-3 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800' : ($flash['type'] === 'error' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') ?>">
                    <?= e($flash['message']) ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Page Content -->
            <main class="p-6">
