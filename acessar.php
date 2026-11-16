<?php
require __DIR__ . '/config/config.php';

$sucesso = '';
$erro = '';
$dados = ['nome' => '', 'empresa' => '', 'telefone' => '', 'documento' => '', 'interesse' => ''];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach ($dados as $campo => $valor) {
        $dados[$campo] = trim($_POST[$campo] ?? '');
    }

    if (in_array('', $dados, true)) {
        $erro = 'Preencha todos os campos para enviar sua solicitação.';
    } elseif (strlen(preg_replace('/\D/', '', $dados['telefone'])) < 10) {
        $erro = 'Informe um telefone válido com DDD.';
    } elseif (strlen(preg_replace('/\D/', '', $dados['documento'])) < 11) {
        $erro = 'Informe um CPF ou CNPJ válido.';
    } else {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS solicitacoes_orcamento (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(120) NOT NULL,
                empresa VARCHAR(150) NOT NULL,
                telefone VARCHAR(30) NOT NULL,
                documento VARCHAR(30) NOT NULL,
                interesse TEXT NOT NULL,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $colunaTelefone = $pdo->query("SHOW COLUMNS FROM solicitacoes_orcamento LIKE 'telefone'")->fetch();
            if (!$colunaTelefone) {
                $pdo->exec('ALTER TABLE solicitacoes_orcamento ADD COLUMN telefone VARCHAR(30) NOT NULL AFTER empresa');
            }

            $stmt = $pdo->prepare('INSERT INTO solicitacoes_orcamento (nome, empresa, telefone, documento, interesse) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$dados['nome'], $dados['empresa'], $dados['telefone'], $dados['documento'], $dados['interesse']]);
            $sucesso = 'Solicitação enviada com sucesso. Nossa equipe entrará em contato para preparar seu orçamento.';
            $dados = array_fill_keys(array_keys($dados), '');
        } catch (PDOException $e) {
            $erro = 'Não foi possível enviar a solicitação agora. Tente novamente em instantes.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acessar a SafeFlow</title>
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime('css/style.css') ?>">
    <link rel="stylesheet" href="css/acessar.css?v=<?= filemtime('css/acessar.css') ?>">
</head>
<body class="access-page">
    <header class="landing-nav">
        <a class="brand" href="index.php">🛡 SafeFlow</a>
        <a class="btn btn-outline" href="index.php">Voltar ao início</a>
    </header>
    <main class="access-content">
        <section class="access-choice">
            <span class="access-kicker">🛡 PORTAL SAFEFLOW</span>
            <h1>Como podemos ajudar você hoje?</h1>
            <p>Escolha o caminho ideal para continuar. É rápido, simples e seguro.</p>
            <div class="access-options">
                <a class="access-option account-option" href="login.php">
                    <span class="option-icon">↗</span>
                    <span>
                        <strong>Já sou cliente</strong>
                        <small>Acesse sua conta e gerencie sua operação.</small>
                    </span>
                    <b>Entrar →</b>
                </a>
                <a class="access-option quote-option" href="#orcamento">
                    <span class="option-icon">✦</span>
                    <span>
                        <strong>Quero conhecer a SafeFlow</strong>
                        <small>Veja os benefícios e solicite um orçamento.</small>
                    </span>
                    <b>Conhecer →</b>
                </a>
            </div>
        </section>
        <section id="orcamento" class="quote-layout">
            <div class="quote-benefits">
                <span class="access-kicker subtle">POR QUE SAFEFLOW?</span>
                <h2>Segurança não precisa ser complicada.</h2>
                <p>Tenha visibilidade de toda a jornada dos seus EPIs e mantenha sua equipe protegida.</p>
                <ul class="check-list">
                    <li>Controle centralizado de EPIs, estoque e certificados.</li>
                    <li>Rastreabilidade das entregas por colaborador.</li>
                    <li>Alertas para vencimentos e reposições necessárias.</li>
                    <li>Informações organizadas para apoiar a conformidade.</li>
                </ul>
            </div>
            <form class="quote-form" method="post">
                <span class="form-step">PASSO 1 DE 1</span>
                <h2>Solicite um orçamento</h2>
                <p>Preencha os dados abaixo. Nossa equipe prepara uma proposta sob medida para sua empresa.</p>
                <?php if ($sucesso): ?><div class="success-message"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>
                <?php if ($erro): ?><div class="error"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
                <label>Nome<input name="nome" value="<?= htmlspecialchars($dados['nome']) ?>" required></label>
                <label>Empresa<input name="empresa" value="<?= htmlspecialchars($dados['empresa']) ?>" required></label>
                <label>Telefone para contato<input name="telefone" type="tel" value="<?= htmlspecialchars($dados['telefone']) ?>" placeholder="(00) 00000-0000" required></label>
                <label>CPF ou CNPJ<input name="documento" value="<?= htmlspecialchars($dados['documento']) ?>" inputmode="numeric" required></label>
                <label>O que você gostaria?<textarea name="interesse" rows="4" placeholder="Ex.: Quero controlar a entrega de EPIs para 80 colaboradores." required><?= htmlspecialchars($dados['interesse']) ?></textarea></label>
                <button class="btn btn-primary full">Enviar solicitação</button>
            </form>
        </section>
    </main>
</body>
</html>
