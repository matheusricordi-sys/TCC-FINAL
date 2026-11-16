<?php

require_once __DIR__ . '/config/config.php';

$mensagem = '';
$tipo_msg = '';
$token_valido = false;
$usuario_id = null;

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $mensagem = 'Token não fornecido.';
    $tipo_msg = 'error';
} else {
    // Verifica se o token é válido e não expirou
    $stmt = $pdo->prepare("SELECT id, nome FROM usuarios WHERE reset_token = ? AND reset_expira > NOW()");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        $mensagem = 'Link inválido ou expirado. <a href="recuperar_senha.php">Solicitar novo link</a>';
        $tipo_msg = 'error';
    } else {
        $token_valido = true;
        $usuario_id = $usuario['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $senha = $_POST['senha'] ?? '';
            $confirma = $_POST['confirma'] ?? '';

            if (empty($senha) || empty($confirma)) {
                $mensagem = 'Preencha todos os campos.';
                $tipo_msg = 'error';
            } elseif (strlen($senha) < 6) {
                $mensagem = 'A senha deve ter pelo menos 6 caracteres.';
                $tipo_msg = 'error';
            } elseif ($senha !== $confirma) {
                $mensagem = 'As senhas não coincidem.';
                $tipo_msg = 'error';
            } else {
                // Atualiza a senha
                $hash_senha = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET senha = ?, reset_token = NULL, reset_expira = NULL WHERE id = ?");
                $stmt->execute([$hash_senha, $usuario_id]);

                $mensagem = 'Senha alterada com sucesso! <a href="login.php" style="color: inherit; font-weight: 700;">Fazer login</a>';
                $tipo_msg = 'success';
                $token_valido = false;
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Redefinir Senha | SafeFlow</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <section class="login-left">
        <a class="brand" href="index.php">🛡 SafeFlow</a>
        <div>
            <span class="eyebrow light">CRIAR NOVA SENHA</span>
            <h1>Proteja sua conta com uma nova senha.</h1>
            <p>Use uma combinação de letras, números e símbolos para maior segurança.</p>
        </div>
        <div class="login-stats">
            <span><b>Mínimo</b> 6 caracteres</span>
            <span><b>Recomendado</b> Misturar tipos</span>
        </div>
    </section>

    <section class="login-right">
        <form class="login-form" method="post">
            <div class="login-logo">🔒</div>
            <h2>Nova Senha</h2>
            <p>Defina uma nova senha para sua conta.</p>

            <?php if ($mensagem): ?>
                <div class="<?= $tipo_msg === 'error' ? 'error' : 'success' ?>" style="padding: 10px; border-radius: 6px; margin: 10px 0; font-size: 13px; line-height: 1.6;">
                    <?= $mensagem ?>
                </div>
            <?php endif; ?>

            <?php if ($token_valido): ?>
                <label>
                    Nova Senha
                    <input type="password" name="senha" required placeholder="Mínimo 6 caracteres">
                </label>

                <label>
                    Confirmar Senha
                    <input type="password" name="confirma" required placeholder="Confirme a nova senha">
                </label>

                <button class="btn btn-primary full">Alterar Senha</button>
            <?php else: ?>
                <div class="login-links" style="margin-top: 20px;">
                    <a href="recuperar_senha.php">Solicitar novo link</a>
                </div>
            <?php endif; ?>
        </form>
    </section>
</body>
</html>