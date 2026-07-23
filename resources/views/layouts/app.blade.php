<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHOP — @yield('title', 'Boutique Pro')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-sm border-b">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="{{ route('pro.dashboard') }}" class="font-bold text-xl text-gray-800">SHOP</a>
            <div class="flex gap-4">
                <a href="{{ route('pro.selection.index') }}" class="text-gray-600 hover:text-gray-900">Ma sélection</a>
                <a href="{{ route('pro.catalog.index') }}" class="text-gray-600 hover:text-gray-900">Catalogue</a>
                <form action="{{ route('pro.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-gray-600 hover:text-gray-900">Déconnexion</button>
                </form>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="bg-gray-100 text-center py-6 text-gray-600 text-sm mt-12">
        <p>SHOP × Planipets — Espace pro © 2026</p>
    </footer>
</body>
</html>
