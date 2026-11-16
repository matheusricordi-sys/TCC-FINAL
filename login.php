
<?php

require_once __DIR__ . '/config/config.php';

if (isset($_SESSION['usuario'])) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha o e-mail e a senha.';
    } else {

        try {

            $stmt = $pdo->prepare(
                "SELECT * FROM usuarios WHERE email = ? LIMIT 1"
            );

            $stmt->execute([$email]);

            $u = $stmt->fetch();

            if ($u) {

                // Compatibilidade com senhas SHA-256 e password_hash
                $senhaValida = password_verify($senha, $u['senha'])
                    || hash_equals(
                        $u['senha'],
                        hash('sha256', $senha)
                    );

                if ($senhaValida) {

                    unset($u['senha']);

                    session_regenerate_id(true);

                    $_SESSION['usuario'] = $u;

                    header('Location: dashboard.php');
                    exit;

                }
            }

            $erro = 'E-mail ou senha inválidos.';

        } catch (PDOException $e) {

            error_log('Erro no login SafeFlow: ' . $e->getMessage());

            $erro = 'Erro ao realizar login. Tente novamente.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login | SafeFlow</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body class="login-page">

    <section class="login-left">

        <a class="brand" href="index.php">🛡 SafeFlow</a>

        <div>
            <span class="eyebrow light">PORTAL DE SEGURANÇA</span>

            <h1>Controle que protege pessoas.</h1>

            <p>
                Acompanhe EPI, CA, estoque e documentação
                em um único lugar.
            </p>
        </div>

        <div class="login-stats">
            <span><b>98,7%</b> conformidade média</span>
            <span><b>1.842</b> EPIs rastreados</span>
            <span><b>99,98%</b> uptime</span>
        </div>

    </section>

    <section class="login-right">

        <form class="login-form" method="post">

            <div class="login-logo">🛡</div>

            <h2>Entrar no sistema</h2>

            <p>Use suas credenciais para acessar o painel.</p>

            <label>
                E-mail
                <input
                    name="email"
                    type="email"
                    placeholder="Digite seu e-mail"
                    value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </label>

            <label>
                Senha
                <input
                    name="senha"
                    type="password"
                    placeholder="Digite sua senha"
                    required
                >
            </label>

            <?php if ($erro): ?>
                <div class="error">
                    <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <button class="btn btn-primary full" type="submit">
                Entrar no SafeFlow
            </button>

            <div class="login-links">
                <a href="recuperar_senha.php">Esqueci a senha</a>
                <a href="registrar.php">Criar conta</a>
            </div>

        </form>

    </section>

</body>
</html>
