<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Forbidden</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex h-full items-center justify-center font-sans antialiased">
    <div class="text-center">
        <p class="text-6xl font-bold text-gray-300">403</p>
        <h1 class="mt-4 text-2xl font-semibold text-gray-800">Access Denied</h1>
        <p class="mt-2 text-sm text-gray-500">You don't have permission to view this page.</p>
        <a href="{{ url('/') }}"
           class="mt-6 inline-block rounded-md bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            Go Home
        </a>
    </div>
</body>
</html>