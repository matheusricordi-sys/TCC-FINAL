<?php

$titulo = 'Compras';
require 'includes/header.php';

if (isset($_POST['aprovar'])) {
    $pdo->prepare("UPDATE pedidos SET status = 'Aprovado' WHERE id = ?")->execute([(int)$_POST['aprovar']]);
    header('Location: compras.php');
    exit;
}

$ped = $pdo->query(
    "SELECT p.*, e.nome epi, f.nome fornecedor 
     FROM pedidos p 
     LEFT JOIN epis e ON e.id = p.epi_id 
     LEFT JOIN fornecedores f ON f.id = p.fornecedor_id 
     ORDER BY p.id DESC"
)->fetchAll();

$for = $pdo->query("SELECT * FROM fornecedores ORDER BY nome")->fetchAll();

?>

<div class="page-head">
    <div>
        <span class="eyebrow">SUPRIMENTOS</span>
        <h1>Compras</h1>
    </div>
    <button class="btn btn-primary" onclick="openModal('modalCompra')">+ Novo pedido</button>
</div>

<div class="cards four">
    <div class="metric">
        <span>Pedidos no mês</span>
        <b><?= count($ped) ?></b>
    </div>
    <div class="metric">
        <span>Aguardando aprovação</span>
        <b><?= count(array_filter($ped, fn($p) => $p['status'] == 'Aguardando')) ?></b>
    </div>
    <div class="metric">
        <span>Valor pendente</span>
        <b>R$ <?= number_format(array_sum(array_map(fn($p) => $p['status'] == 'Aguardando' ? $p['valor'] : 0, $ped)), 2, ',', '.') ?></b>
    </div>
    <div class="metric">
        <span>Total gasto</span>
        <b>R$ 42.680,00</b>
    </div>
</div>

<section class="panel">
    <h2>Pedidos de compra</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nº Pedido</th>
                    <th>EPI</th>
                    <th>Fornecedor</th>
                    <th>Qtd.</th>
                    <th>Valor</th>
                    <th>Data</th>
                    <th>Prazo</th>
                    <th>Status</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ped as $p): ?>
                    <tr>
                        <td>
                            <b><?= $p['numero'] ?></b>
                        </td>
                        <td><?= htmlspecialchars($p['epi']) ?></td>
                        <td><?= htmlspecialchars($p['fornecedor']) ?></td>
                        <td><?= $p['quantidade'] ?></td>
                        <td>R$ <?= number_format($p['valor'], 2, ',', '.') ?></td>
                        <td><?= date('d/m/Y', strtotime($p['data_pedido'])) ?></td>
                        <td><?= $p['prazo'] ? date('d/m/Y', strtotime($p['prazo'])) : '-' ?></td>
                        <td>
                            <span class="pill <?= $p['status'] == 'Aguardando' ? 'amber' : ($p['status'] == 'Recebido' ? 'green' : 'blue') ?>">
                                <?= $p['status'] ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($p['status'] == 'Aguardando'): ?>
                                <form method="post">
                                    <button class="btn-mini" name="aprovar" value="<?= $p['id'] ?>">Aprovar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <h2>Fornecedores</h2>
    <div class="supplier-grid">
        <?php foreach ($for as $f): ?>
            <div class="supplier">
                <b><?= htmlspecialchars($f['nome']) ?></b>
                <span class="pill green">Ativo</span>
                <p>
                    <?= $f['cnpj'] ?><br>
                    <?= $f['email'] ?><br>
                    <?= $f['telefone'] ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div id="modalCompra" class="modal">
    <div class="modal-box">
        <button class="close" onclick="closeModal('modalCompra')">×</button>
        <h2>Novo pedido</h2>
        <form method="post" action="salvar_pedido.php" class="form-grid">
            <label>
                EPI
                <select name="epi_id">
                    <?php foreach ($pdo->query("SELECT id, nome FROM epis") as $e): ?>
                        <option value="<?= $e['id'] ?>">
                            <?= htmlspecialchars($e['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Fornecedor
                <select name="fornecedor_id">
                    <?php foreach ($for as $f): ?>
                        <option value="<?= $f['id'] ?>">
                            <?= htmlspecialchars($f['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Quantidade
                <input name="quantidade" type="number" value="10" required>
            </label>

            <label>
                Valor
                <input name="valor" type="number" step="0.01" value="0" required>
            </label>

            <label>
                Prazo
                <input name="prazo" type="date">
            </label>

            <label>
                Observações
                <textarea name="observacoes"></textarea>
            </label>

            <button class="btn btn-primary">Criar pedido</button>
        </form>
    </div>
</div>

<script src="js/modals.js"></script>

<?php require 'includes/footer.php'; ?>