<!-- Sidebar -->
<aside class="w-64 bg-slate-900 text-white flex flex-col justify-between p-4 shrink-0">
    <div>
        <div class="flex items-center space-x-2 px-2 py-3 mb-6 border-b border-slate-800">
            <span class="text-emerald-500 text-2xl">⚕️</span>
            <span class="text-lg font-bold tracking-tight">PharmaFEFO</span>
        </div>
        <nav class="space-y-1">
            <a href="index.php?route=dashboard" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg bg-emerald-600 text-white font-medium transition-colors">
                <span>📊</span> <span>Dashboard</span>
            </a>
            <a href="index.php?route=stock-receive" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                <span>📥</span> <span>Stock Ingestion</span>
            </a>
            <a href="index.php?route=stock-dispatch" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                <span>📤</span> <span>Dispense Medicine</span>
            </a>
            <a href="index.php?route=reports" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                <span>📉</span> <span>Financial Reports</span>
            </a>
        </nav>
    </div>

    <div class="border-t border-slate-800 pt-4 px-2 space-y-3">
        <div class="flex items-center space-x-3">
            <div class="w-9 h-9 rounded-full bg-emerald-500 flex items-center justify-center text-sm font-bold text-white uppercase shrink-0">
                <?= htmlspecialchars(substr($currentUser ?? 'U', 0, 2)) ?>
            </div>
            <div class="truncate">
                <p class="text-sm font-semibold truncate max-w-[150px]"><?= htmlspecialchars($currentUser ?? 'User') ?></p>
                <p class="text-xs text-slate-400 capitalize"><?= htmlspecialchars($userRole ?? 'Preparator') ?></p>
            </div>
        </div>

        <a href="index.php?route=logout"
           class="flex items-center justify-center w-full px-3 py-2 text-sm font-medium text-slate-400 hover:text-red-400 bg-slate-800/50 hover:bg-slate-800 rounded-lg transition-colors duration-150 gap-2">
            <span>🚪</span> Logout
        </a>
    </div>
</aside>