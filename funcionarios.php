<?php

$titulo = 'Funcionários';
require 'includes/header.php';

if (isset($_POST['acao'])) {
    if ($_POST['acao'] === 'salvar') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $st = $pdo->prepare("UPDATE funcionarios SET nome = ?, cargo = ?, setor = ?, matricula = ?, status = ? WHERE id = ?");
            $st->execute([$_POST['nome'], $_POST['cargo'], $_POST['setor'], $_POST['matricula'], $_POST['status'], $id]);
        } else {
            $st = $pdo->prepare("INSERT INTO funcionarios(nome, cargo, setor, matricula, status) VALUES(?, ?, ?, ?, ?)");
            $st->execute([$_POST['nome'], $_POST['cargo'], $_POST['setor'], $_POST['matricula'], $_POST['status']]);
        }
    }
    if ($_POST['acao'] === 'excluir') {
        $pdo->prepare("DELETE FROM funcionarios WHERE id = ?")->execute([(int)$_POST['id']]);
    }
    header('Location: funcionarios.php');
    exit;
}

$q = trim($_GET['q'] ?? '');
$setor = $_GET['setor'] ?? 'Todos';
$sql = "SELECT * FROM funcionarios WHERE nome LIKE ?";
$params = ["%$q%"];

if ($setor !== 'Todos') {
    $sql .= " AND setor = ?";
    $params[] = $setor;
}

$sql .= " ORDER BY nome";
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

?>

<div class="page-head">
    <div>
        <span class="eyebrow">PESSOAS</span>
        <h1>Funcionários</h1>
    </div>
    <button class="btn btn-primary" onclick="openModal('modalFuncionario')">+ Novo funcionário</button>
</div>

<section class="panel">
    <form class="filters">
        <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por nome...">
        <select name="setor">
            <option>Todos</option>
            <?php foreach (['Produção', 'Manutenção', 'Logística', 'SESMT'] as $s): ?>
                <option <?= $setor === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-secondary">Filtrar</button>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Funcionário</th>
                    <th>Cargo</th>
                    <th>Setor</th>
                    <th>Status</th>
                    <th>EPIs</th>
                    <th>CA vencendo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td>
                            <b><?= htmlspecialchars($r['nome']) ?></b>
                            <small><?= $r['matricula'] ?></small>
                        </td>
                        <td><?= htmlspecialchars($r['cargo']) ?></td>
                        <td><?= $r['setor'] ?></td>
                        <td>
                            <span class="pill <?= strtolower($r['status']) ?>">
                                <?= $r['status'] ?>
                            </span>
                        </td>
                        <td><?= $r['epis_atribuidos'] ?></td>
                        <td><?= $r['ca_vencendo'] ?></td>
                        <td class="actions">
                            <button onclick='editFuncionario(<?= json_encode($r) ?>)'>Editar</button>
                            <form method="post" onsubmit="return confirm('Excluir este funcionário?')">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button>Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<div id="modalFuncionario" class="modal">
    <div class="modal-box">
        <button class="close" onclick="closeModal('modalFuncionario')">×</button>
        <h2 id="funcTitle">Novo funcionário</h2>
        <form method="post" class="form-grid">
            <input type="hidden" name="acao" value="salvar">
            <input id="funcId" type="hidden" name="id">

            <label>
                Nome
                <input id="funcNome" name="nome" required>
            </label>

            <label>
                Cargo
                <input id="funcCargo" name="cargo" required>
            </label>

            <label>
                Setor
                <select id="funcSetor" name="setor">
                    <option>Produção</option>
                    <option>Manutenção</option>
                    <option>Logística</option>
                    <option>SESMT</option>
                </select>
            </label>

            <label>
                Matrícula
                <input id="funcMat" name="matricula" required>
            </label>

            <label>
                Status
                <select id="funcStatus" name="status">
                    <option>Ativo</option>
                    <option>Férias</option>
                    <option>Inativo</option>
                </select>
            </label>

            <button class="btn btn-primary">Salvar</button>
        </form>
    </div>
</div>

<script src="js/modals.js"></script>

<?php require 'includes/footer.php'; ?>