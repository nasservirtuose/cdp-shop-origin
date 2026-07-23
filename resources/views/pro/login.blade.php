<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>SHOP — Connexion pro</title>
</head>
<body>
    @if (session('error')) <p style="color:red">{{ session('error') }}</p> @endif
    <h1>Espace pro SHOP</h1>
    <p>La connexion se fait via Planipets.</p>
    <a href="{{ $planipetsUrl }}">Se connecter via Planipets</a>
</body>
</html>