<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHOP — @yield('title', 'Espace pro')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/css/shop.css">
</head>
<body>
    <nav class="nav">
        <div class="nav-in">
            <a href="{{ route('pro.dashboard') }}" class="brand"><span class="mark">S</span>shop</a>
            <div class="nav-links">
                <a href="{{ route('pro.catalog.index') }}">Catalogue</a>
                <a href="{{ route('pro.selection.index') }}">Ma sélection</a>
                <form action="{{ route('pro.logout') }}" method="POST" style="display:inline">@csrf<button type="submit" class="linkbtn">Déconnexion</button></form>
            </div>
        </div>
    </nav>
    <main>@yield('content')</main>
    <footer class="foot">SHOP × Planipets — Espace pro</footer>
</body>
</html>
