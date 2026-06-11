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
    <aside class="w-64 bg-slate-900 text-white flex flex-col justify-between p-4 shrink-0">
        <div>
            <div class="flex items-center space-x-2 px-2 py-3 mb-6 border-b border-slate-800">
                <span class="text-emerald-500 text-2xl">⚕️</span>
                <span class="text-lg font-bold tracking-tight">PharmaFEFO</span>
            </div>
            <nav class="space-y-1">
                <a href="/dashboard" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg bg-emerald-600 text-white font-medium transition-colors">
                    <span>📊</span> <span>Dashboard</span>
                </a>
                <a href="/stock/entry" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                    <span>📥</span> <span>Stock Ingestion</span>
                </a>
                <a href="/stock/exit" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                    <span>📤</span> <span>Dispense Medicine</span>
                </a>
                <a href="/reports" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                    <span>📉</span> <span>Financial Reports</span>
                </a>
            </nav>
        </div>

        <div class="border-t border-slate-800 pt-4 px-2 flex items-center space-x-3">
            <div class="w-9 h-9 rounded-full bg-emerald-500 flex items-center justify-center text-sm font-bold text-white">
                JD
            </div>
            <div>
                <p class="text-sm font-semibold truncate max-w-[140px]">John Doe</p>
                <p class="text-xs text-slate-400">Head Pharmacist</p>
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
                <span class="absolute top-1 right-1 flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                    </span>
            </div>
        </header>

        <section class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                <p class="text-sm font-medium text-slate-500">Total Tracked Batches</p>
                <p class="text-3xl font-bold text-slate-900 mt-2">1,482</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 border-l-4 border-emerald-500 shadow-xs">
                <p class="text-sm font-medium text-slate-500">Conforming (> 6 months)</p>
                <p class="text-3xl font-bold text-emerald-600 mt-2">1,425</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 border-l-4 border-amber-500 shadow-xs">
                <p class="text-sm font-medium text-slate-500">Warning (< 90 days)</p>
                <p class="text-3xl font-bold text-amber-600 mt-2">43</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 border-l-4 border-red-500 shadow-xs">
                <p class="text-sm font-medium text-slate-500">Critical (< 30 days)</p>
                <p class="text-3xl font-bold text-red-600 mt-2">14</p>
            </div>
        </section>

        <section class="bg-white p-4 rounded-xl border border-slate-200 mb-6 flex flex-col sm:flex-row justify-between items-center gap-4">
            <form action="/dashboard" method="GET" class="flex items-center space-x-3 w-full sm:w-auto">
                <label for="status-filter" class="text-sm font-medium text-slate-700 whitespace-nowrap">Filter Grid:</label>
                <select id="status-filter" name="status" onchange="this.form.submit()"
                        class="block w-full sm:w-48 px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="all">All Batches</option>
                    <option value="critical" selected>🔴 Critical Alert (&lt; 30 days)</option>
                    <option value="warning">🟠 Warning Alert (&lt; 90 days)</option>
                    <option value="ok">🟢 Conforming (&gt; 6 months)</option>
                </select>
            </form>
            <div class="flex gap-2 w-full sm:w-auto">
                <a href="/stock/entry" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition-colors cursor-pointer">
                    + Ingest New Batch
                </a>
            </div>
        </section>

        <section class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Medication Name</th>
                        <th class="px-6 py-4">Batch ID</th>
                        <th class="px-6 py-4">Available Qty</th>
                        <th class="px-6 py-4">Expiration Date (DLU)</th>
                        <th class="px-6 py-4">Risk Evaluation</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">

                    <tr class="bg-red-50/50 hover:bg-red-50 transition-colors">
                        <td class="px-6 py-4 font-semibold text-slate-900">Amoxicillin 500mg (Capsules)</td>
                        <td class="px-6 py-4"><span class="font-mono bg-slate-100 text-slate-700 px-2 py-0.5 rounded border border-slate-200 text-xs">AMX-2026-09</span></td>
                        <td class="px-6 py-4 font-medium text-slate-900">14 boxes</td>
                        <td class="px-6 py-4 text-red-700 font-medium">June 30, 2026</td>
                        <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                                        Critical (&lt; 30 Days)
                                    </span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <button class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors cursor-pointer">
                                Flag: Expired / Destroy
                            </button>
                        </td>
                    </tr>

                    <tr class="bg-amber-50/30 hover:bg-amber-50 transition-colors">
                        <td class="px-6 py-4 font-semibold text-slate-900">Paracetamol 1g (Effervescent)</td>
                        <td class="px-6 py-4"><span class="font-mono bg-slate-100 text-slate-700 px-2 py-0.5 rounded border border-slate-200 text-xs">PAR-8821</span></td>
                        <td class="px-6 py-4 font-medium text-slate-900">110 boxes</td>
                        <td class="px-6 py-4 text-amber-700 font-medium">August 15, 2026</td>
                        <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                        Warning (&lt; 90 Days)
                                    </span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <button class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-amber-900 bg-amber-100 hover:bg-amber-200 rounded-lg transition-colors cursor-pointer mr-2">
                                Supplier Return
                            </button>
                        </td>
                    </tr>

                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-semibold text-slate-900">Lantus Solostar 100 U/mL</td>
                        <td class="px-6 py-4"><span class="font-mono bg-slate-100 text-slate-700 px-2 py-0.5 rounded border border-slate-200 text-xs">LAN-4110</span></td>
                        <td class="px-6 py-4 font-medium text-slate-900">45 packs</td>
                        <td class="px-6 py-4 text-slate-600">February 12, 2027</td>
                        <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Conforming
                                    </span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <span class="text-xs text-slate-400 italic">No actions needed</span>
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

</body>
</html>