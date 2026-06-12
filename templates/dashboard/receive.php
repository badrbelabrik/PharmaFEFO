<?php
declare(strict_types=1);

// Default values if not set by controller
$currentUser = $currentUser ?? 'User';
$userRole = $userRole ?? 'preparator';
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingest Stock - PharmaFEFO</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="h-full text-slate-900">
<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col p-4 shrink-0">
        <div class="flex items-center space-x-2 px-2 py-3 mb-6 border-b border-slate-800">
            <span class="text-emerald-500 text-2xl">⚕️</span>
            <span class="text-lg font-bold">PharmaFEFO</span>
        </div>
        <nav class="space-y-1">
            <a href="index.php?route=dashboard" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 transition-colors">
                <span>📊</span> <span>Dashboard</span>
            </a>
            <a href="index.php?route=stock-receive" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg bg-emerald-600 text-white font-medium">
                <span>📥</span> <span>Stock Ingestion</span>
            </a>
            <a href="index.php?route=stock-dispatch" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 transition-colors">
                <span>📤</span> <span>Dispense Medicine</span>
            </a>
            <?php if ($userRole === 'pharmacist' || $userRole === 'admin'): ?>
                <a href="index.php?route=reports" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 transition-colors">
                    <span>📉</span> <span>Financial Reports</span>
                </a>
            <?php endif; ?>
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

    <!-- Form Content -->
    <main class="flex-grow p-8 max-w-3xl">
        <header class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900">Intelligent Stock Ingestion</h1>
            <p class="text-sm text-slate-500 mt-1">Log incoming shipments. Batches are automatically organized into FEFO evaluation pipelines.</p>
        </header>

        <!-- Success Message -->
        <?php if (isset($successMessage)): ?>
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-lg text-sm">
                <div class="flex items-center">
                    <span class="text-lg mr-2">✅</span>
                    <span><?= htmlspecialchars($successMessage) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if (isset($errorMessage)): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg text-sm">
                <div class="flex items-center">
                    <span class="text-lg mr-2">❌</span>
                    <span><?= htmlspecialchars($errorMessage) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Validation Errors Array -->
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg text-sm">
                <div class="font-semibold mb-2">Please fix the following errors:</div>
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="index.php?route=stock-receive" method="POST" class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-6">

            <!-- Product Selection -->
            <div>
                <label for="product_id" class="block text-sm font-medium text-slate-700 mb-1">
                    Select Medication Reference <span class="text-red-500">*</span>
                </label>
                <select id="product_id" name="product_id" required
                        class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none focus:border-emerald-500">
                    <option value="">-- Choose Medication --</option>
                    <?php foreach ($products as $prod): ?>
                        <option value="<?= $prod->getId() ?>" <?= (isset($_POST['product_id']) && $_POST['product_id'] == $prod->getId()) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($prod->getName()) ?> (SN: <?= htmlspecialchars($prod->getSerialNumber()) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Lot Number & Expiration Date -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="lot_number" class="block text-sm font-medium text-slate-700 mb-1">
                        Lot Number / Batch ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="lot_number" name="lot_number" required
                           value="<?= htmlspecialchars($_POST['lot_number'] ?? '') ?>"
                           placeholder="e.g., LOT-2026-X99"
                           class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none focus:border-emerald-500 font-mono">
                    <p class="mt-1 text-xs text-slate-400">Unique identifier from manufacturer</p>
                </div>

                <div>
                    <label for="expiration_date" class="block text-sm font-medium text-slate-700 mb-1">
                        Expiration Date (DLU) <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="expiration_date" name="expiration_date" required
                           value="<?= htmlspecialchars($_POST['expiration_date'] ?? '') ?>"
                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                           class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none focus:border-emerald-500">
                    <p class="mt-1 text-xs text-slate-400">Cannot be today or in the past</p>
                </div>
            </div>

            <!-- Quantity & Purchase Price -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="quantity" class="block text-sm font-medium text-slate-700 mb-1">
                        Quantity Received (units) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="quantity" name="quantity" min="1" required
                           value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>"
                           placeholder="100"
                           class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label for="purchase_price" class="block text-sm font-medium text-slate-700 mb-1">
                        Unit Purchase Price (€) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="purchase_price" name="purchase_price" step="0.01" min="0.00" required
                           value="<?= htmlspecialchars($_POST['purchase_price'] ?? '') ?>"
                           placeholder="4.50"
                           class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none focus:border-emerald-500">
                </div>
            </div>

            <!-- Batch Summary Preview -->
            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                <h3 class="text-sm font-semibold text-slate-700 mb-2 flex items-center">
                    <span class="text-lg mr-2">📋</span> Batch Summary Preview
                </h3>
                <div class="text-xs text-slate-500 space-y-1">
                    <p>• This batch will be prioritized in FEFO dispensing queue based on expiration date</p>
                    <p>• Status will be automatically calculated based on days until expiration:</p>
                    <div class="flex gap-4 mt-2 ml-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-800">&gt; 90 days: Active</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-amber-100 text-amber-800">30-90 days: Warning</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-red-100 text-red-800">&lt; 30 days: Critical</span>
                    </div>
                    <p class="mt-2">• System will send notifications at 90 and 30 days before expiration</p>
                    <p>• Stock movement will be recorded for audit trail</p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                <a href="index.php?route=dashboard" class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition-colors cursor-pointer">
                    ✅ Confirm Ingestion
                </button>
            </div>
        </form>
    </main>
</div>
</body>
</html>