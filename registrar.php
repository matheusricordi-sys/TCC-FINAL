
<?php

require_once __DIR__ . '/config/config.php';

$mensagem = '';
$tipo_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirma = $_POST['confirma'] ?? '';

    if ($nome === '' || $email === '' || $senha === '' || $confirma === '') {

        $mensagem = 'Preencha todos os campos.';
        $tipo_msg = 'error';

    } elseif (strlen($senha) < 6) {

        $mensagem = 'A senha deve ter pelo menos 6 caracteres.';
        $tipo_msg = 'error';

    } elseif ($senha !== $confirma) {

        $mensagem = 'As senhas não coincidem.';
        $tipo_msg = 'error';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $mensagem = 'E-mail inválido.';
        $tipo_msg = 'error';

    } else {

        try {

            // Verifica se o e-mail já existe
            $stmt = $pdo->prepare(
                "SELECT id FROM usuarios WHERE email = ? LIMIT 1"
            );

            $stmt->execute([$email]);

            if ($stmt->fetch()) {

                $mensagem = 'Este e-mail já está cadastrado.';
                $tipo_msg = 'error';

            } else {

                // Mantém compatibilidade com o login atual
                $hash_senha = password_hash($senha, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO usuarios
                    (nome, email, senha, perfil, status)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $nome,
                    $email,
                    $hash_senha,
                    'Usuario',
                    'Ativo'
                ]);

                $mensagem = 'Conta criada com sucesso!';
                $tipo_msg = 'success';

            }

               } catch (PDOException $e) {

                	error_log('Erro no registro SafeFlow: ' . $e->getMessage());

               		$mensagem = 'Erro MySQL: ' . $e->getMessage();
                	$tipo_msg = 'error';
            }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Criar Conta | SafeFlow</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        .register-page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #020617;
            color: white;
            align-items: center;
        }

        .register-page .login-left {
            padding: 40px 7%;
            height: auto;
        }

        .register-form {
            width: min(100%, 500px);
        }

        .register-form .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .register-form label {
            display: block;
            color: #cbd5e1;
            font-weight: 700;
            margin: 10px 0 5px 0;
        }

        .register-form input,
        .register-form select {
            display: block;
            width: 100%;
            padding: 11px;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 10px;
            color: white;
            font-size: 14px;
            outline: none;
        }

        .register-form input:focus,
        .register-form select:focus {
            border-color: #1d4ed8;
        }

        .checkbox-label {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin: 15px 0;
            font-weight: 400;
            font-size: 12px;
        }

        .checkbox-label input {
            width: auto;
            margin-top: 4px;
        }

        @media (max-width: 900px) {

            .register-page {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body class="register-page">

    <section class="login-left">

        <a class="brand" href="index.php">🛡 SafeFlow</a>

        <div>

            <span class="eyebrow light">COMECE AGORA</span>

            <h1>Proteja seus EPIs com o SafeFlow.</h1>

            <p>
                Crie sua conta e comece a gerenciar equipamentos
                de proteção individual de forma centralizada e segura.
            </p>

        </div>

        <div class="login-stats">

            <span><b>✓</b> Controle total de estoque</span>

            <span><b>✓</b> Rastreabilidade completa</span>

            <span><b>✓</b> Conformidade NR-6</span>

        </div>

    </section>

    <section class="login-right">

        <form class="register-form" method="post">

            <div class="login-logo">📋</div>

            <h2>Criar Conta</h2>

            <p>Preencha os dados abaixo para começar.</p>

            <?php if ($mensagem): ?>

                <div
                    class="<?= $tipo_msg === 'error' ? 'error' : 'success' ?>"
                    style="padding: 10px; border-radius: 6px; margin: 10px 0; font-size: 13px; line-height: 1.6;"
                >

                    <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>

                    <?php if ($tipo_msg === 'success'): ?>

                        <br>

                        <a
                            href="login.php"
                            style="color: inherit; font-weight: 700;"
                        >
                            Acessar login
                        </a>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

            <div class="form-grid">

                <label>
                    Nome Completo

                    <input
                        type="text"
                        name="nome"
                        required
                        placeholder="João da Silva"
                        value="<?= htmlspecialchars($_POST['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    >
                </label>

                <label>
                    E-mail

                    <input
                        type="email"
                        name="email"
                        required
                        placeholder="seu@empresa.com.br"
                        value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    >
                </label>

                <label>
                    Senha

                    <input
                        type="password"
                        name="senha"
                        required
                        minlength="6"
                        placeholder="Mínimo 6 caracteres"
                    >
                </label>

                <label>
                    Confirmar Senha

                    <input
                        type="password"
                        name="confirma"
                        required
                        minlength="6"
                        placeholder="Confirme sua senha"
                    >
                </label>

                <div class="checkbox-label">

                    <input
                        type="checkbox"
                        id="termos"
                        required
                    >

                    <label for="termos" style="margin: 0;">
                        Concordo com os Termos de Serviço e Política de Privacidade
                    </label>

                </div>

                <button
                    class="btn btn-primary full"
                    type="submit"
                    style="margin-top: 10px;"
                >
                    Criar minha conta
                </button>

                <div style="text-align: center; margin-top: 15px; font-size: 13px;">

                    Já tem conta?

                    <a
                        href="login.php"
                        style="color: #60a5fa; font-weight: 700; text-decoration: none;"
                    >
                        Fazer login
                    </a>

                </div>

            </div>

        </form>

    </section>

</body>
</html>
