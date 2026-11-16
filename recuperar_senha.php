<?php

require_once __DIR__ . '/config/config.php';

$mensagem = '';
$tipo_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $mensagem = 'Por favor, digite seu e-mail.';
        $tipo_msg = 'error';
    } else {
        // Verifica se o email existe no banco
        $stmt = $pdo->prepare("SELECT id, nome FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            $mensagem = 'E-mail não encontrado no sistema.';
            $tipo_msg = 'error';
        } else {
            // Gera um token único
            $token = bin2hex(random_bytes(32));
            $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Salva o token no banco
            $stmt = $pdo->prepare("UPDATE usuarios SET reset_token = ?, reset_expira = ? WHERE id = ?");
            $stmt->execute([$token, $expiracao, $usuario['id']]);

            // Simular envio de email (em produção, usar PHPMailer ou similar)
            $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            $baseUrl = ($https ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '') . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
            $link_reset = $baseUrl . '/resetar_senha.php?token=' . urlencode($token);
            
            // Aqui você poderia usar mail() ou uma biblioteca como PHPMailer
            // mail($email, "Recuperação de Senha - SafeFlow", 
            //     "Olá {$usuario['nome']},\n\n" .
            //     "Clique no link abaixo para redefinir sua senha:\n" .
            //     $link_reset . "\n\n" .
            //     "Este link expira em 1 hora.\n\n" .
            //     "Atenciosamente,\nEquipe SafeFlow");

            // Para fins de demonstração, exibir o link
            $mensagem = 'Instruções enviadas! Copie o link abaixo:<br><code style="display:block; margin-top:10px; padding:10px; background:#f1f5f9; border-radius:6px; word-break:break-all;">' . htmlspecialchars($link_reset) . '</code>';
            $tipo_msg = 'success';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar Senha | SafeFlow</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <section class="login-left">
        <a class="brand" href="index.php">🛡 SafeFlow</a>
        <div>
            <span class="eyebrow light">RECUPERAÇÃO DE ACESSO</span>
            <h1>Receba instruções para redefinir sua senha.</h1>
            <p>Digite seu e-mail cadastrado e receberá um link para criar uma nova senha de forma segura.</p>
        </div>
        <div class="login-stats">
            <span><b>Seguro</b> Protocolo de segurança</span>
            <span><b>Rápido</b> Link valido por 1 hora</span>
        </div>
    </section>

    <section class="login-right">
        <form class="login-form" method="post">
            <div class="login-logo">🔑</div>
            <h2>Esqueci a Senha</h2>
            <p>Insira o e-mail da sua conta.</p>

            <label>
                E-mail
                <input type="email" name="email" required placeholder="seu@empresa.com.br">
            </label>

            <?php if ($mensagem): ?>
                <div class="<?= $tipo_msg === 'error' ? 'error' : 'success' ?>" style="padding: 10px; border-radius: 6px; margin: 10px 0; font-size: 13px; line-height: 1.6;">
                    <?= $mensagem ?>
                </div>
            <?php endif; ?>

            <button class="btn btn-primary full">Enviar instruções</button>

            <div class="login-links" style="margin-top: 20px;">
                <a href="login.php">Voltar ao login</a>
            </div>
        </form>
    </section>
</body>
</html>