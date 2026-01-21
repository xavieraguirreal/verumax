<?php
/**
 * VERUMAX SUPER ADMIN - Sidebar
 */

if (!defined('VERUMAX_ADMIN_PATH')) {
    die('Acceso denegado');
}

$menu_items = [
    ['name' => 'Dashboard', 'icon' => 'home', 'url' => 'index.php', 'page' => 'index'],
    ['name' => 'Clientes', 'icon' => 'users', 'url' => 'clientes.php', 'page' => 'clientes'],
    ['name' => 'Planes', 'icon' => 'credit-card', 'url' => 'planes.php', 'page' => 'planes'],
    ['name' => 'Configuración', 'icon' => 'cog', 'url' => 'configuracion.php', 'page' => 'configuracion'],
];

$tools_items = [
    ['name' => 'Template Editor', 'icon' => 'pencil', 'url' => '../tools/template-editor/', 'external' => true],
    ['name' => 'Template Manager', 'icon' => 'template', 'url' => '../tools/template-manager/', 'external' => true],
];

$icons = [
    'home' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
    'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
    'credit-card' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
    'cog' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
    'pencil' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
    'template' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>',
];
?>
<aside class="fixed left-0 top-0 h-full w-64 bg-slate-900 text-white shadow-xl z-50">
    <!-- Logo -->
    <div class="p-6 border-b border-slate-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-lg">VERUMax</h2>
                <p class="text-xs text-slate-400">Super Admin</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="p-4 space-y-1">
        <?php foreach ($menu_items as $item): ?>
        <a
            href="<?= e($item['url']) ?>"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?= $current_page === $item['page'] ? 'bg-purple-600 text-white' : 'text-slate-300 hover:bg-slate-800' ?>"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <?= $icons[$item['icon']] ?>
            </svg>
            <span><?= e($item['name']) ?></span>
        </a>
        <?php endforeach; ?>

        <!-- Separador Tools -->
        <div class="pt-4 mt-4 border-t border-slate-700">
            <p class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Herramientas</p>
        </div>

        <?php foreach ($tools_items as $item): ?>
        <a
            href="<?= e($item['url']) ?>"
            target="_blank"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition text-slate-300 hover:bg-slate-800"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <?= $icons[$item['icon']] ?>
            </svg>
            <span><?= e($item['name']) ?></span>
            <svg class="w-3 h-3 ml-auto text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>
        <?php endforeach; ?>
    </nav>

    <!-- Footer -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-700">
        <div class="text-xs text-slate-500 text-center">
            VERUMax v<?= VERUMAX_ADMIN_VERSION ?>
        </div>
    </div>
</aside>
