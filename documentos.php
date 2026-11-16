<?php

$titulo = 'Digitalização';
require 'includes/header.php';

if (isset($_POST['upload']) && isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
    $dir = __DIR__ . '/uploads';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $nome = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['arquivo']['name']);
    move_uploaded_file($_FILES['arquivo']['tmp_name'], $dir . '/' . $nome);

    $st = $pdo->prepare(
        "INSERT INTO documentos(funcionario_id, tipo, arquivo, paginas, status, data_documento) 
         VALUES(?, ?, ?, ?, ?, CURDATE())"
    );
    $st->execute([$_POST['funcionario_id'], $_POST['tipo'], $nome, 1, 'Válido']);

    header('Location: documentos.php');
    exit;
}

$docs = $pdo->query(
    "SELECT d.*, f.nome funcionario 
     FROM documentos d 
     LEFT JOIN funcionarios f ON f.id = d.funcionario_id 
     ORDER BY d.id DESC"
)->fetchAll();

$func = $pdo->query("SELECT id, nome FROM funcionarios ORDER BY nome")->fetchAll();

?>

<div class="page-head">
    <div>
        <span class="eyebrow">DOCUMENTOS</span>
        <h1>Digitalização</h1>
    </div>
    <span class="muted">OCR e rastreabilidade documental</span>
</div>

<div class="document-grid">
    <section class="panel">
        <h2>Nova digitalização</h2>
        <form method="post" enctype="multipart/form-data" class="form-grid">
            <label>
                Funcionário
                <select name="funcionario_id">
                    <?php foreach ($func as $f): ?>
                        <option value="<?= $f['id'] ?>">
                            <?= htmlspecialchars($f['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Tipo
                <select name="tipo">
                    <option>Ficha de EPI</option>
                    <option>Termo de Responsabilidade</option>
                    <option>Declaração de Uso</option>
                    <option>Recibo de Devolução</option>
                    <option>Laudo de Descarte</option>
                </select>
            </label>

            <label class="upload">
                <input type="file" name="arquivo" accept=".pdf,.jpg,.jpeg,.png,.tiff" required>
                <strong>📤 Arraste ou selecione o arquivo</strong>
                <small>PDF, JPG, PNG ou TIFF</small>
            </label>

            <button name="upload" class="btn btn-primary">Processar documento</button>
        </form>
    </section>

    <section class="panel">
        <h2>Requisitos NR-6</h2>
        <ul class="check-list">
            <li>Nome completo</li>
            <li>Matrícula</li>
            <li>CA do equipamento</li>
            <li>Data</li>
            <li>Assinatura</li>
        </ul>
        <div class="notice">
            Boas práticas: 300 DPI, sem dobras, fundo branco e PDF/A.
        </div>
    </section>
</div>

<section class="panel">
    <h2>Documentos digitalizados</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Funcionário</th>
                    <th>Tipo</th>
                    <th>Data</th>
                    <th>Arquivo</th>
                    <th>Pág.</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($docs as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['funcionario']) ?></td>
                        <td><?= $d['tipo'] ?></td>
                        <td><?= date('d/m/Y', strtotime($d['data_documento'])) ?></td>
                        <td><?= htmlspecialchars($d['arquivo']) ?></td>
                        <td><?= $d['paginas'] ?></td>
                        <td>
                            <span class="pill green">
                                <?= $d['status'] ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require 'includes/footer.php'; ?>