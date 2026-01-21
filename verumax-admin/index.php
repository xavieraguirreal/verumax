<?php
/**
 * VERUMAX SUPER ADMIN - Dashboard
 */

require_once __DIR__ . '/config.php';
require_once VERUMAX_ADMIN_PATH . '/includes/auth.php';

use VERUMaxAdmin\Database;

$page_title = 'Dashboard';

// Obtener estadísticas
try {
    $stats = [
        'total_clientes' => 0,
        'clientes_activos' => 0,
        'clientes_test' => 0,
        'clientes_produccion' => 0,
    ];

    // Contar clientes (cuando exista la tabla)
    $clientes = Database::query("SELECT COUNT(*) as total,
        SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
        SUM(CASE WHEN modo = 'test' THEN 1 ELSE 0 END) as test,
        SUM(CASE WHEN modo = 'produccion' THEN 1 ELSE 0 END) as produccion
        FROM instancias");

    if (!empty($clientes)) {
        $stats['total_clientes'] = $clientes[0]['total'] ?? 0;
        $stats['clientes_activos'] = $clientes[0]['activos'] ?? 0;
        $stats['clientes_test'] = $clientes[0]['test'] ?? 0;
        $stats['clientes_produccion'] = $clientes[0]['produccion'] ?? 0;
    }
} catch (\Exception $e) {
    // Las tablas aún no existen
    $stats = [
        'total_clientes' => 0,
        'clientes_activos' => 0,
        'clientes_test' => 0,
        'clientes_produccion' => 0,
    ];
}

include VERUMAX_ADMIN_PATH . '/includes/header.php';
?>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Clientes -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Clientes</p>
                <p class="text-3xl font-bold text-gray-800"><?= $stats['total_clientes'] ?></p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Clientes Activos -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Clientes Activos</p>
                <p class="text-3xl font-bold text-green-600"><?= $stats['clientes_activos'] ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Clientes Test -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Modo Test</p>
                <p class="text-3xl font-bold text-amber-600"><?= $stats['clientes_test'] ?></p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Clientes Producción -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Producción</p>
                <p class="text-3xl font-bold text-blue-600"><?= $stats['clientes_produccion'] ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Acciones Rápidas</h2>
    <div class="flex flex-wrap gap-3">
        <a href="clientes.php?action=new" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span>Nuevo Cliente</span>
        </a>
        <a href="clientes.php" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            <span>Ver Clientes</span>
        </a>
        <a href="planes.php" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span>Gestionar Planes</span>
        </a>
    </div>
</div>

<!-- Info Panel -->
<div class="bg-amber-50 border border-amber-200 rounded-xl p-6">
    <div class="flex gap-4">
        <div class="flex-shrink-0">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h3 class="font-medium text-amber-800 mb-1">Panel en Desarrollo</h3>
            <p class="text-sm text-amber-700">
                Este panel está en fase inicial. Algunas funcionalidades estarán disponibles próximamente.
                Para comenzar, ejecute el SQL de inicialización en la base de datos.
            </p>
        </div>
    </div>
</div>

<?php include VERUMAX_ADMIN_PATH . '/includes/footer.php'; ?>
