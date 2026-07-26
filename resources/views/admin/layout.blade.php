<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHOP Admin - @yield('title', 'Catalogue')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/shop.css">
    <style>        .lbl{display:block;font-size:15px;font-weight:600;color:var(--ink);margin:16px 0 6px}        .hint{font-size:14px;color:var(--muted);margin-top:6px;line-height:1.55}        .desc{font-size:15px;line-height:1.6}        .field{font-size:15.5px}        select.field{cursor:pointer}    </style>
</head>
<body>
    <nav class="nav" style="background:#08472A">
        <div class="nav-in">
            <a href="{{ route('admin.categories.index') }}" class="brand"><span class="mark">S</span>shop . admin</a>
            <div class="nav-links">
                <a href="{{ route('admin.categories.index') }}">Categories</a>
                <a href="{{ route('admin.products.index') }}">Produits</a>
                <a href="{{ route('admin.orders.index') }}">Commandes</a>
                <form action="{{ route('admin.logout') }}" method="POST" style="display:inline">@csrf<button type="submit" class="linkbtn">Deconnexion</button></form>
            </div>
        </div>
    </nav>
    <main class="page">
        @if (session('success'))<div class="flash flash-ok">{{ session('success') }}</div>@endif
        @yield('content')
    </main>
    <footer class="foot">SHOP Admin - gestion du catalogue</footer>
</body>
</html>
