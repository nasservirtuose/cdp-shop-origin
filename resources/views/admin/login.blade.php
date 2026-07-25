<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHOP Admin - Connexion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/shop.css">
</head>
<body>
    <div class="login-wrap">
        <div class="login-card">
            <div class="mk">S</div>
            <h1>Administration SHOP</h1>
            <p>Connecte-toi pour gerer le catalogue.</p>
            @if ($errors->any())<div class="flash flash-err">{{ $errors->first() }}</div>@endif
            <form action="{{ route('login') }}" method="POST" style="text-align:left">
                @csrf
                <label style="display:block;font-size:13px;font-weight:600;color:var(--muted);margin-bottom:6px">Email</label>
                <input class="field" type="email" name="email" value="{{ old('email') }}" required style="margin-bottom:14px">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--muted);margin-bottom:6px">Mot de passe</label>
                <input class="field" type="password" name="password" required style="margin-bottom:20px">
                <button class="btn btn-primary btn-block" style="padding:13px">Se connecter</button>
            </form>
        </div>
    </div>
</body>
</html>
