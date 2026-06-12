<?php
declare(strict_types=1);

use PharmaFEFO\Service\StockBatchService;
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PharmaFEFO</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="h-full font-sans antialiased text-slate-900">

<div class="flex min-h-screen">
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

        <div class="border-t border-slate-800 pt-4 px-2 flex items-center space-x-3">
            <div class="w-9 h-9 rounded-full bg-emerald-500 flex items-center justify-center text-sm font-bold text-white">
                <?= strtoupper(substr($currentUser ?? 'U', 0, 2)) ?>
            </div>
            <div>
                <p class="text-sm font-semibold truncate max-w-[140px]"><?= htmlspecialchars($currentUser ?? 'User') ?></p>
                <p class="text-xs text-slate-400"><?= htmlspecialchars($userRole ?? 'Preparator') ?></p>
            </div>
        </div>
    </aside>

    <main class="flex-grow p-8 overflow-y-auto">
        <header class="flex justify-between items-center pb-6 border-b border-slate-200 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Dispensary Overview</h1>
                <p class="text-sm text-slate-500">Monitor batch queues and anticipate upcoming losses.</p>
            </div>
            <div class="relative p-2 bg-white rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50 transition-colors">
                <span class="text-xl">🔔</span>
                <?php $unreadCount = $unreadNotificationsCount ?? 0; ?>
                <?php if ($unreadCount > 0): ?>
                    <span class="absolute top-1 right-1 flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                    </span>
                <?php endif; ?>
            </div>
        </header>

        <!-- Stats Cards -->
        <section class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                <p class="text-sm font-medium text-slate-500">Total Tracked Batches</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?= $stats['totalBatches'] ?? 0 ?></p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 border-l-4 border-emerald-500 shadow-xs">
                <p class="text-sm font-medium text-slate-500">Conforming (> 6 months)</p>
                <p class="text-3xl font-bold text-emerald-600 mt-2"><?= $stats['healthyCount'] ?? 0 ?></p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 border-l-4 border-amber-500 shadow-xs">
                <p class="text-sm font-medium text-slate-500">Warning (< 90 days)</p>
                <p class="text-3xl font-bold text-amber-600 mt-2"><?= $stats['warningCount'] ?? 0 ?></p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 border-l-4 border-red-500 shadow-xs">
                <p class="text-sm font-medium text-slate-500">Critical (< 30 days)</p>
                <p class="text-3xl font-bold text-red-600 mt-2"><?= $stats['criticalCount'] ?? 0 ?></p>
            </div>
        </section>

        <!-- Filter Section -->
        <section class="bg-white p-4 rounded-xl border border-slate-200 mb-6 flex flex-col sm:flex-row justify-between items-center gap-4">
            <form action="index.php" method="GET" class="flex items-center space-x-3 w-full sm:w-auto">
                <input type="hidden" name="route" value="dashboard">
                <label for="status-filter" class="text-sm font-medium text-slate-700 whitespace-nowrap">Filter Grid:</label>
                <select id="status-filter" name="filter" onchange="this.form.submit()"
                        class="block w-full sm:w-48 px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="all" <?= ($currentFilter ?? 'all') === 'all' ? 'selected' : '' ?>>All Batches</option>
                    <option value="critical" <?= ($currentFilter ?? '') === 'critical' ? 'selected' : '' ?>>🔴 Critical Alert (&lt; 30 days)</option>
                    <option value="warning" <?= ($currentFilter ?? '') === 'warning' ? 'selected' : '' ?>>🟠 Warning Alert (&lt; 90 days)</option>
                    <option value="healthy" <?= ($currentFilter ?? '') === 'healthy' ? 'selected' : '' ?>>🟢 Conforming (&gt; 6 months)</option>
                </select>
            </form>
            <div class="flex gap-2 w-full sm:w-auto">
                <a href="index.php?route=stock-receive" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition-colors cursor-pointer">
                    + Ingest New Batch
                </a>
            </div>
        </section>

        <!-- Batches Table -->
        <section class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Medication Name</th>
                        <th class="px-6 py-4">Lot Number</th>
                        <th class="px-6 py-4">Available Qty</th>
                        <th class="px-6 py-4">Expiration Date (DLU)</th>
                        <th class="px-6 py-4">Risk Evaluation</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                    <?php if (empty($displayBatches)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                No batches found matching the selected filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($displayBatches as $batch): ?>
                            <?php
                            // Use StockBatchService for all calculations
                            $daysLeft = StockBatchService::getDaysUntilExpiration($batch);
                            $badgeClass = StockBatchService::getBadgeClass($batch);
                            $statusLabel = StockBatchService::getStatusLabel($batch);
                            $isExpired = StockBatchService::isExpired($batch);

                            // Determine row class based on days left
                            if ($isExpired || $daysLeft < 0) {
                                $rowClass = 'bg-gray-50/50 hover:bg-gray-50';
                                $dateClass = 'text-gray-500 line-through';
                            } elseif ($daysLeft <= 30) {
                                $rowClass = 'bg-red-50/50 hover:bg-red-50';
                                $dateClass = 'text-red-700 font-medium';
                            } elseif ($daysLeft <= 90) {
                                $rowClass = 'bg-amber-50/30 hover:bg-amber-50';
                                $dateClass = 'text-amber-700 font-medium';
                            } else {
                                $rowClass = 'hover:bg-slate-50';
                                $dateClass = 'text-slate-600';
                            }
                            ?>
                            <tr class="<?= $rowClass ?> transition-colors">
                                <td class="px-6 py-4 font-semibold text-slate-900">
                                    <?= htmlspecialchars($batch->getProduct()->getName()) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-mono bg-slate-100 text-slate-700 px-2 py-0.5 rounded border border-slate-200 text-xs">
                                        <?= htmlspecialchars($batch->getLotNumber()) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-900">
                                    <?= $batch->getQuantity() ?> units
                                </td>
                                <td class="px-6 py-4 <?= $dateClass ?>">
                                    <?= StockBatchService::formatExpirationDate($batch, 'F j, Y') ?>
                                    <?php if ($isExpired || $daysLeft < 0): ?>
                                        <span class="ml-2 text-xs text-red-600">(Expired)</span>
                                    <?php elseif ($daysLeft <= 30): ?>
                                        <span class="ml-2 text-xs text-red-600">(<?= $daysLeft ?> days left)</span>
                                    <?php elseif ($daysLeft <= 90): ?>
                                        <span class="ml-2 text-xs text-amber-600">(<?= $daysLeft ?> days left)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $badgeClass ?> border">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <?php if ($isExpired || $daysLeft < 0): ?>
                                        <form action="index.php?route=stock-expired" method="POST" class="inline">
                                            <input type="hidden" name="batch_id" value="<?= $batch->getId() ?>">
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-gray-600 hover:bg-gray-700 rounded-lg transition-colors cursor-pointer">
                                                Remove from Stock
                                            </button>
                                        </form>
                                    <?php elseif ($daysLeft <= 30): ?>
                                        <form action="index.php?route=stock-expired" method="POST" class="inline">
                                            <input type="hidden" name="batch_id" value="<?= $batch->getId() ?>">
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors cursor-pointer">
                                                Flag: Expired / Destroy
                                            </button>
                                        </form>
                                    <?php elseif ($daysLeft <= 90): ?>
                                        <button class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-amber-900 bg-amber-100 hover:bg-amber-200 rounded-lg transition-colors cursor-pointer mr-2">
                                            Supplier Return
                                        </button>
                                        <a href="index.php?route=stock-dispatch&product_id=<?= $batch->getProduct()->getId() ?>"
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors cursor-pointer">
                                            Dispense
                                        </a>
                                    <?php else: ?>
                                        <a href="index.php?route=stock-dispatch&product_id=<?= $batch->getProduct()->getId() ?>"
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors cursor-pointer">
                                            Dispense
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

</body>
</html>