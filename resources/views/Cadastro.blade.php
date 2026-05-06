<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body class="auth-page">
    <main class="card">
        <h1 class="logo">Cadastro</h1>

        <form  method="POST" action="{{ route('submitCadastro') }}">
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Email" required>
            </div>

            <div class="field">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="password" placeholder="Senha" required>
            </div>

            <div class="field">
                <label for="confirmar_senha">Confirmar senha</label>
                <input type="password" id="confirmar_senha" name="password_confirmation" placeholder="Confirmar senha" required>
            </div>

            <p id="senha-erro" style="display:none; color:#da1a29; margin:6px 0 12px;">
                As senhas não conferem.
            </p>

            <button id="btn-criar-conta" class="btn btn-secondary" type="submit" disabled>Criar conta</button>
        </form>
    </main>

    <script>
        const senhaInput = document.getElementById('senha');
        const confirmarSenhaInput = document.getElementById('confirmar_senha');
        const btnCriarConta = document.getElementById('btn-criar-conta');
        const senhaErro = document.getElementById('senha-erro');

        function validarSenhas() {
            const senha = senhaInput.value;
            const confirmar = confirmarSenhaInput.value;
            const preenchidas = senha.length > 0 && confirmar.length > 0;
            const iguais = senha === confirmar;

            btnCriarConta.disabled = !preenchidas || !iguais;
            senhaErro.style.display = preenchidas && !iguais ? 'block' : 'none';
        }

        senhaInput.addEventListener('input', validarSenhas);
        confirmarSenhaInput.addEventListener('input', validarSenhas);

        document.querySelector('form').addEventListener('submit', function (event) {
            if (senhaInput.value !== confirmarSenhaInput.value) {
                event.preventDefault();
                senhaErro.style.display = 'block';
                btnCriarConta.disabled = true;
            }
        });
    </script>
</body>
</html>
