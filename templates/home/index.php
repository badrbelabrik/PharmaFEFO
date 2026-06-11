<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to PharmaFEFO - Intelligent Inventory Control</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="min-h-full flex flex-col justify-between text-slate-800">

<header class="bg-white border-b border-slate-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <span class="text-emerald-600 text-3xl">⚕️</span>
            <span class="text-xl font-bold tracking-tight text-slate-900">PharmaFEFO</span>
        </div>
        <div>
            <a href="index.php?route=login"
               class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all cursor-pointer">
                Sign In
            </a>
        </div>
    </div>
</header>

<main class="flex-grow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 text-center lg:text-left lg:flex lg:items-center lg:justify-between lg:gap-12">

        <div class="max-w-2xl mx-auto lg:mx-0">
                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800 mb-4">
                    First Expired, First Out Standard
                </span>
            <h1 class="text-4xl font-extrabold text-slate-900 sm:text-5xl tracking-tight">
                Eliminate Pharmaceutical Waste. <br>
                <span class="text-emerald-600">Secure Patient Safety.</span>
            </h1>
            <p class="mt-4 text-lg text-slate-600 leading-relaxed">
                PharmaFEFO automates inventory priority by enforcing strict expiration queues. Prevent financial losses, eliminate human error during dispensing, and anticipate shortages months in advance.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row justify-center lg:justify-start gap-4">
                <a href="index.php?route=login"
                   class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-xl shadow-md text-white bg-emerald-600 hover:bg-emerald-700 transition-all cursor-pointer">
                    Access Dispensary System
                </a>
                <a href="#features"
                   class="inline-flex items-center justify-center px-6 py-3 border border-slate-300 text-base font-medium rounded-xl text-slate-700 bg-white hover:bg-slate-50 transition-all">
                    Learn More
                </a>
            </div>
        </div>

        <div class="mt-12 lg:mt-0 max-w-md mx-auto lg:max-w-none w-full lg:w-1/2 bg-white rounded-2xl shadow-xl p-6 border border-slate-100">
            <div class="flex justify-between items-center border-b border-slate-100 pb-4 mb-4">
                <h3 class="font-bold text-slate-900">Real-time Batch Criticality</h3>
                <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded font-semibold animate-pulse">Action Required</span>
            </div>
            <div class="space-y-3">
                <div class="p-3 bg-red-50 rounded-lg flex justify-between items-center border-l-4 border-red-500">
                    <div>
                        <p class="text-sm font-bold text-slate-900">Amoxicillin 500mg</p>
                        <p class="text-xs text-slate-500">Batch: AMX-2026-09</p>
                    </div>
                    <span class="text-xs font-semibold text-red-600 px-2 py-1 bg-white rounded border border-red-200">&lt; 30 Days left</span>
                </div>
                <div class="p-3 bg-amber-50 rounded-lg flex justify-between items-center border-l-4 border-amber-500">
                    <div>
                        <p class="text-sm font-bold text-slate-900">Paracetamol 1g</p>
                        <p class="text-xs text-slate-500">Batch: PAR-8821</p>
                    </div>
                    <span class="text-xs font-semibold text-amber-600 px-2 py-1 bg-white rounded border border-amber-200">&lt; 90 Days left</span>
                </div>
            </div>
        </div>
    </div>

    <section id="features" class="bg-white border-t border-slate-200 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <h2 class="text-3xl font-bold text-slate-900 tracking-tight">Designed for Compliance & Safety</h2>
                <p class="mt-2 text-slate-600">Enforcing hospital-grade inventory workflows in commercial pharmacies.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-6 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="text-emerald-600 text-2xl mb-3">📥</div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Smart Ingestion</h3>
                    <p class="text-sm text-slate-600">Forces complete data logging at order reception. Mandatory batch identification and expiration safeguards prevent invalid entries.</p>
                </div>
                <div class="p-6 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="text-emerald-600 text-2xl mb-3">🔄</div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">The FEFO Core</h3>
                    <p class="text-sm text-slate-600">The application algorithms automatically dictate which exact batch to pull from drawers first, protecting inventory lifecycle valuation.</p>
                </div>
                <div class="p-6 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="text-emerald-600 text-2xl mb-3">📈</div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Predictive Audits</h3>
                    <p class="text-sm text-slate-600">Provides administrators with automated financial loss evaluations monthly to refine purchase orders and optimize clinical overhead.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="bg-slate-900 text-slate-400 py-8 text-center text-sm border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p>&copy; 2026 PharmaFEFO. Secure Regulatory Traceability Application.</p>
    </div>
</footer>

</body>
</html>
