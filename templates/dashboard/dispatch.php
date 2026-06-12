<?php
declare(strict_types=1);

$currentUser = $currentUser ?? 'User';
$userRole = $userRole ?? 'preparator';
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispense Medicine - PharmaFEFO</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="h-full text-slate-900">
<div class="flex min-h-screen">
    <!-- Sidebar (same as receive view) -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col p-4 shrink-0">
        <div class="flex items-center space-x-2 px-2 py-3 mb-6 border-b border-slate-800">
            <span class="text-emerald-500 text-2xl">⚕️</span>
            <span class="text-lg font-bold">PharmaFEFO</span>
        </div>
        <nav class="space-y-1">
            <a href="index.php?route=dashboard" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 transition-colors">
                <span>📊</span> <span>Dashboard</span>
            </a>
            <a href="index.php?route=stock-receive" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 transition-colors">
                <span>📥</span> <span>Stock Ingestion</span>
            </a>
            <a href="index.php?route=stock-dispatch" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg bg-emerald-600 text-white font-medium">
                <span>📤</span> <span>Dispense Medicine</span>
            </a>
        </nav>
        <div class="border-t border-slate-800 pt-4 mt-auto px-2 flex items-center space-x-3">
            <div class="w-9 h-9 rounded-full bg-emerald-500 flex items-center justify-center text-sm font-bold text-white">
                <?= strtoupper(substr($currentUser, 0, 2)) ?>
            </div>
            <div>
                <p class="text-sm font-semibold truncate max-w-[140px]"><?= htmlspecialchars($currentUser) ?></p>
                <p class="text-xs text-slate-400"><?= ucfirst(htmlspecialchars($userRole)) ?></p>
            </div>
        </div>
    </aside>

    <main class="flex-grow p-8 max-w-3xl">
        <header class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900">Dispense Medication (FEFO)</h1>
            <p class="text-sm text-slate-500 mt-1">System automatically selects the soonest expiring batch (First Expired, First Out).</p>
        </header>

        <?php if (isset($successMessage)): ?>
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-lg text-sm">
                ✅ <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errorMessage)): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg text-sm">
                ❌ <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <form action="index.php?route=stock-dispatch" method="POST" class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-6">

            <div>
                <label for="product_id" class="block text-sm font-medium text-slate-700 mb-1">
                    Select Medication <span class="text-red-500">*</span>
                </label>
                <select id="product_id" name="product_id" required
                        onchange="this.form.submit()"
                        class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    <option value="">-- Choose Medication --</option>
                    <?php foreach ($products as $prod): ?>
                        <option value="<?= $prod->getId() ?>" <?= (isset($selectedProductId) && $selectedProductId == $prod->getId()) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($prod->getName()) ?> (SN: <?= htmlspecialchars($prod->getSerialNumber()) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (isset($selectedBatch) && $selectedBatch): ?>
                <!-- FEFO Batch Information -->
                <div class="bg-emerald-50 rounded-lg p-4 border border-emerald-200">
                    <h3 class="text-sm font-semibold text-emerald-800 mb-2">🎯 FEFO Selection Result</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-slate-600">Batch Number:</span>
                            <span class="font-mono font-semibold"><?= htmlspecialchars($selectedBatch->getLotNumber()) ?></span>
                        </div>
                        <div>
                            <span class="text-slate-600">Expiration Date:</span>
                            <span class="font-semibold text-amber-600"><?= $selectedBatch->getExpirationDate()->format('Y-m-d') ?></span>
                        </div>
                        <div>
                            <span class="text-slate-600">Available Quantity:</span>
                            <span class="font-semibold"><?= $selectedBatch->getQuantity() ?> units</span>
                        </div>
                        <div>
                            <span class="text-slate-600">Days until expiry:</span>
                            <span class="font-semibold <?= $daysLeft <= 30 ? 'text-red-600' : ($daysLeft <= 90 ? 'text-amber-600' : 'text-emerald-600') ?>">
                                <?= $daysLeft ?> days
                            </span>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="quantity" class="block text-sm font-medium text-slate-700 mb-1">
                        Quantity to Dispense <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="quantity" name="quantity" min="1" max="<?= $selectedBatch->getQuantity() ?>" required
                           value="1"
                           class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    <p class="mt-1 text-xs text-slate-400">Max available: <?= $selectedBatch->getQuantity() ?> units</p>
                </div>

                <input type="hidden" name="batch_id" value="<?= $selectedBatch->getId() ?>">
                <input type="hidden" name="product_id" value="<?= $selectedProductId ?>">

                <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                    <a href="index.php?route=dashboard" class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg">Cancel</a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg">✅ Confirm Dispensation</button>
                </div>
            <?php elseif (isset($selectedProductId) && $selectedProductId): ?>
                <div class="bg-amber-50 rounded-lg p-4 border border-amber-200 text-amber-800">
                    ⚠️ No stock available for this medication. Please receive new stock first.
                </div>
            <?php endif; ?>
        </form>
    </main>
</div>
</body>
</html>