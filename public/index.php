<?php

require_once __DIR__ . '/../config/autoloader.php';

?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - PharmaFEFO</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">

<div class="sm:mx-auto w-full sm:max-w-md">
    <div class="flex justify-center text-emerald-600 text-5xl">
        ⚕️
    </div>
    <h2 class="mt-4 text-center text-3xl font-extrabold text-slate-900 tracking-tight">
        PharmaFEFO
    </h2>
    <p class="mt-2 text-center text-sm text-slate-600">
        Stock Management & Expiry Date Tracking System
    </p>
</div>

<div class="mt-8 sm:mx-auto w-full sm:max-w-md">
    <div class="bg-white py-8 px-4 shadow-xl rounded-xl sm:px-10 border border-slate-100">

        <form class="space-y-6" action="/login" method="POST">

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">
                    Professional Email Address
                </label>
                <div class="mt-1">
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm placeholder-slate-400 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                           placeholder="john.doe@pharma.com">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">
                    Password
                </label>
                <div class="mt-1">
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm placeholder-slate-400 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                           placeholder="••••••••">
                </div>
            </div>


            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox"
                           class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-slate-300 rounded-sm">
                    <label for="remember-me" class="ml-2 block text-sm text-slate-900">
                        Remember me
                    </label>
                </div>

                <div class="text-sm">
                    <a href="#" class="font-medium text-emerald-600 hover:text-emerald-500">
                        Forgot credentials?
                    </a>
                </div>
            </div>

            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors duration-150 cursor-pointer">
                    Sign In
                </button>
            </div>
        </form>

    </div>

    <p class="mt-6 text-center text-xs text-slate-400">
        Secure system compliant with health traceability and public safety standards (Strict FEFO rule).
    </p>
</div>

</body>
</html>
