<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHOP — Connexion pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/shop.css">
</head>
<body>
    <div class="login-wrap">
        <div class="login-card">
            <div class="mk">S</div>
            <h1>Espace pro SHOP</h1>
            <p>La connexion se fait via Planipets.</p>
            @if (session('error'))<div class="flash flash-err">{{ session('error') }}</div>@endif
            <a href="{{ $planipetsUrl }}" class="btn btn-primary btn-block" style="padding:13px">Se connecter via Planipets</a>
        </div>
    </div>
</body>
</html>