<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHOP — @yield('title', 'Espace Pro')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Red Hat Display"', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand:         '#26914A',
                        'brand-dark':  '#0A6533',
                        'brand-deep':  '#044402',
                        'brand-light': '#ECF5EC',
                        'brand-lime':  '#61B546',
                        gold:          '#EAB308',
                        ink:           '#1A1A1A',
                        // remap des utilitaires déjà utilisés dans les vues → charte Planipets
                        green:  { 50:'#ECF5EC', 100:'#ECF5EC', 500:'#26914A', 600:'#0A6533', 700:'#044402', 800:'#044402' },
                        blue:   { 500:'#3858F6', 600:'#2E49D6', 700:'#1E3AAE', 800:'#1E3AAE' },
                        yellow: { 50:'#FEF6E0', 100:'#FCEFC7', 500:'#EAB308', 600:'#CA8A04', 800:'#854D0E' },
                    },
                },
            },
        }
    </script>
    <style> body { background-color: #F0F2F5; } </style>
</head>
<body class="font-sans text-ink">
    <nav class="bg-brand text-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="{{ route('pro.dashboard') }}" class="font-black text-xl tracking-tight">SHOP</a>
            <div class="flex items-center gap-6 text-sm font-bold">
                <a href="{{ route('pro.selection.index') }}" class="hover:text-brand-light transition">Ma sélection</a>
                <a href="{{ route('pro.catalog.index') }}" class="hover:text-brand-light transition">Catalogue</a>
                <form action="{{ route('pro.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="hover:text-brand-light transition">Déconnexion</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="min-h-screen">
        @yield('content')
    </main>

    <footer class="bg-white border-t text-center py-6 text-gray-500 text-sm mt-12">
        <p>SHOP × Planipets — Espace pro © 2026</p>
    </footer>
</body>
</html>
