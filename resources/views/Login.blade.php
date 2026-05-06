<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body class="auth-page">
    <main class="card">
        <h1 class="logo">Login</h1>

        <form method="POST" action="{{ route('teste') }}">
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Email" required>
            </div>

            <div class="field">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" placeholder="Senha" required>
            </div>

            <button class="btn btn-primary" type="submit">Entrar</button>
            <a class="help" href="#">Recuperar senha</a>

            <hr class="divider">
        </form>

        <button class="btn btn-secondary" type="button" onclick="window.location='{{ route('cadastro') }}'">Criar conta</button>
    </main>
</body>
</html>
