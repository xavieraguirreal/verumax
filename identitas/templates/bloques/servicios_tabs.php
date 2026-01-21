<?php
/**
 * Bloque: Servicios en Tabs/Pestañas
 * Template: Con Pestañas para Servicios
 */

$categorias = $contenido['categorias'] ?? [];
$tab_style = $config['tab_style'] ?? 'pills';
?>

<div class="py-8" x-data="{ activeTab: 0 }">
    <!-- Tabs Navigation -->
    <div class="flex flex-wrap gap-2 mb-8 <?php echo $tab_style === 'pills' ? 'justify-center' : 'border-b border-gray-200 dark:border-gray-700'; ?>">
        <?php foreach ($categorias as $index => $cat): ?>
        <button onclick="document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden')); document.getElementById('tab-panel-<?php echo $index; ?>').classList.remove('hidden'); document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active')); this.classList.add('active');"
                class="tab-btn <?php echo $index === 0 ? 'active' : ''; ?> flex items-center gap-2 px-5 py-3 font-medium transition
                       <?php if ($tab_style === 'pills'): ?>
                       rounded-full hover:bg-gray-100 dark:hover:bg-gray-700
                       <?php else: ?>
                       border-b-2 border-transparent hover:border-gray-300
                       <?php endif; ?>">
            <i data-lucide="<?php echo htmlspecialchars($cat['icono'] ?? 'folder'); ?>" class="w-5 h-5"></i>
            <?php echo htmlspecialchars($cat['nombre'] ?? ''); ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- Tab Panels -->
    <?php foreach ($categorias as $index => $cat): ?>
    <div id="tab-panel-<?php echo $index; ?>" class="tab-panel <?php echo $index !== 0 ? 'hidden' : ''; ?>">
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php
            $servicios = array_filter(array_map('trim', explode("\n", $cat['servicios'] ?? '')));
            foreach ($servicios as $servicio):
            ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-100 dark:border-gray-700 hover:shadow-md transition flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0" style="color: var(--color-primario);"></i>
                <span class="text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($servicio); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<style>
.tab-btn.active {
    background: var(--color-primario);
    color: white;
}
.tab-btn:not(.active) {
    color: #6b7280;
}
</style>
