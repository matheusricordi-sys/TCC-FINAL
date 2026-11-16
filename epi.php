<?php

$titulo = 'Controle de EPI';
require 'includes/header.php';

if (isset($_POST['acao'])) {
    if ($_POST['acao'] === 'salvar') {
        $id = (int)($_POST['id'] ?? 0);
        $v = DateTime::createFromFormat('d/m/Y', $_POST['validade']);
        $date = $v ? $v->format('Y-m-d') : $_POST['validade'];

        if ($id) {
            $st = $pdo->prepare("UPDATE epis SET nome = ?, ca = ?, categoria = ?, estoque = ?, estoque_minimo = ?, validade_ca = ? WHERE id = ?");
            $st->execute([$_POST['nome'], $_POST['ca'], $_POST['categoria'], $_POST['estoque'], $_POST['minimo'], $date, $id]);
        } else {
            $st = $pdo->prepare("INSERT INTO epis(nome, ca, categoria, estoque, estoque_minimo, validade_ca) VALUES(?, ?, ?, ?, ?, ?)");
            $st->execute([$_POST['nome'], $_POST['ca'], $_POST['categoria'], $_POST['estoque'], $_POST['minimo'], $date]);
        }
    }
    if ($_POST['acao'] === 'excluir') {
        $pdo->prepare("DELETE FROM epis WHERE id = ?")->execute([(int)$_POST['id']]);
    }
    header('Location: epi.php');
    exit;
}

$f = $_GET['status'] ?? 'Todos';
$rows = $pdo->query("SELECT * FROM epis ORDER BY nome")->fetchAll();

function statusEpi($r) {
    if ($r['estoque'] < $r['estoque_minimo']) {
        return ['Crítico', 'red'];
    }
    $dias = (strtotime($r['validade_ca']) - time()) / 86400;
    return $dias <= 30 ? ['Vencendo', 'amber'] : ['Regular', 'green'];
}

?>

<div class="page-head">
    <div>
        <span class="eyebrow">EQUIPAMENTOS</span>
        <h1>Controle de EPI</h1>
    </div>
    <button class="btn btn-primary" onclick="openModal('modalEpi')">+ Novo EPI</button>
</div>

<div class="cards three">
    <div class="metric">
        <span>Total de tipos</span>
        <b><?= count($rows) ?></b>
    </div>
    <div class="metric">
        <span>Estoque crítico</span>
        <b><?= count(array_filter($rows, fn($r) => statusEpi($r)[0] == 'Crítico')) ?></b>
    </div>
    <div class="metric">
        <span>CA vencendo</span>
        <b><?= count(array_filter($rows, fn($r) => statusEpi($r)[0] == 'Vencendo')) ?></b>
    </div>
</div>

<section class="panel">
    <div class="tabs">
        <?php foreach (['Todos', 'Regular', 'Vencendo', 'Crítico'] as $x): ?>
            <a class="<?= $f === $x ? 'selected' : '' ?>" href="?status=<?= urlencode($x) ?>">
                <?= $x ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>EPI</th>
                    <th>CA</th>
                    <th>Categoria</th>
                    <th>Estoque</th>
                    <th>Validade</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r):
                    $s = statusEpi($r);
                    if ($f !== 'Todos' && $f !== $s[0]) {
                        continue;
                    }
                ?>
                    <tr>
                        <td>
                            <b><?= htmlspecialchars($r['nome']) ?></b>
                        </td>
                        <td><?= $r['ca'] ?></td>
                        <td><?= $r['categoria'] ?></td>
                        <td class="<?= $s[0] == 'Crítico' ? 'danger-text' : '' ?>">
                            <b><?= $r['estoque'] ?></b> / mín. <?= $r['estoque_minimo'] ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($r['validade_ca'])) ?></td>
                        <td>
                            <span class="pill <?= $s[1] ?>">
                                <?= $s[0] ?>
                            </span>
                        </td>
                        <td class="actions">
                            <button onclick='editEpi(<?= json_encode($r) ?>)'>Editar</button>
                            <form method="post" onsubmit="return confirm('Excluir este EPI?')">
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

<div id="modalEpi" class="modal">
    <div class="modal-box">
        <button class="close" onclick="closeModal('modalEpi')">×</button>
        <h2 id="epiTitle">Novo EPI</h2>
        <form method="post" class="form-grid">
            <input type="hidden" name="acao" value="salvar">
            <input id="epiId" type="hidden" name="id">

            <label>
                Nome
                <input id="epiNome" name="nome" required>
            </label>

            <label>
                Nº CA
                <input id="epiCa" name="ca" required>
            </label>

            <label>
                Categoria
                <select id="epiCat" name="categoria">
                    <?php foreach (['Cabeça', 'Olhos/Face', 'Audição', 'Respiratório', 'Mãos/Braços', 'Tronco', 'Pés/Pernas', 'Quedas'] as $x): ?>
                        <option><?= $x ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Estoque
                <input id="epiEst" type="number" name="estoque" required>
            </label>

            <label>
                Mínimo
                <input id="epiMin" type="number" name="minimo" required>
            </label>

            <label>
                Validade CA
                <input id="epiVal" name="validade" placeholder="DD/MM/AAAA" required>
            </label>

            <button class="btn btn-primary">Salvar EPI</button>
        </form>
    </div>
</div>

<script src="js/modals.js"></script>

<?php require 'includes/footer.php'; ?>