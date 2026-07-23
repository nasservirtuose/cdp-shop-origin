<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHOP — Connexion pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style> body { font-family:'Red Hat Display', sans-serif; background:#F0F2F5; } </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-md p-8 max-w-md w-full text-center">
        @if (session('error'))
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm">{{ session('error') }}</div>
        @endif
        <h1 class="text-2xl font-black mb-2" style="color:#0A6533">Espace pro SHOP</h1>
        <p class="text-gray-500 mb-6">La connexion se fait via Planipets.</p>
        <a href="{{ $planipetsUrl }}" class="inline-block w-full text-white font-bold py-3 rounded-lg" style="background:#26914A">
            Se connecter via Planipets
        </a>
    </div>
</body>
</html>