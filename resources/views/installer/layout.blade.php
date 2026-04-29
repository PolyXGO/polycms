<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PolyCMS Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #f8fafc; color: #334155; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="bg-indigo-600 p-6 text-white text-center">
            <h1 class="text-2xl font-bold tracking-tight">PolyCMS Installer</h1>
            <p class="text-indigo-200 text-sm mt-1">Easily set up your application</p>
        </div>
        <div class="p-8">
            @yield('content')
        </div>
    </div>
</body>
</html>
