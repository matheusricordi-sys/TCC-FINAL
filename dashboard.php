<?php

$titulo = 'Dashboard';
require 'includes/header.php';

$func = $pdo->query("SELECT COUNT(*) n FROM funcionarios WHERE status = 'Ativo'")->fetch()['n'];
$epi = $pdo->query("SELECT COUNT(*) n FROM epis")->fetch()['n'];
$crit = $pdo->query("SELECT COUNT(*) n FROM epis WHERE estoque < estoque_minimo")->fetch()['n'];
$venc = $pdo->query("SELECT COUNT(*) n FROM epis WHERE validade_ca BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch()['n'];

?>

<div class="page-head">
    <div>
        <span class="eyebrow">VISÃO GERAL</span>
        <h1>Dashboard</h1>
    </div>
    <span class="muted"><?= date('d/m/Y') ?></span>
</div>

<div class="cards four">
    <div class="metric">
        <span>Funcionários Ativos</span>
        <b><?= $func ?></b>
        <em class="green">+6 este mês</em>
    </div>
    <div class="metric">
        <span>EPIs Cadastrados</span>
        <b><?= $epi ?></b>
        <em class="green">+12 novos</em>
    </div>
    <div class="metric">
        <span>Estoque Crítico</span>
        <b><?= $crit ?></b>
        <em class="red">Abaixo do mínimo</em>
    </div>
    <div class="metric">
        <span>CA Vencendo</span>
        <b><?= $venc ?></b>
        <em class="amber">Próximos 30 dias</em>
    </div>
</div>

<div class="dashboard-grid">
    <section class="panel">
        <h2>Alertas prioritários</h2>
        <?php
        $alerts = [
            ['red', 'CA do Protetor Facial vence em breve'],
            ['red', 'Estoque de Luva de Vaqueta abaixo do mínimo'],
            ['amber', 'Funcionários com CA vencendo em setembro'],
            ['amber', 'Pedido PC-2026-081 aguardando aprovação'],
            ['blue', 'Ficha de EPI digitalizada']
        ];
        foreach ($alerts as $a):
        ?>
            <div class="alert">
                <i class="<?= $a[0] ?>"></i>
                <span><?= $a[1] ?></span>
                <small>Hoje</small>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="panel">
        <h2>EPIs por status</h2>
        <?php
        $total = max(1, $epi);
        $regs = $pdo->query("SELECT COUNT(*) n FROM epis WHERE estoque >= estoque_minimo AND validade_ca > DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch()['n'];
        $cr = $crit;
        $ven = max(0, $total - $regs - $cr);

        foreach ([
            ['Regular', $regs, 'green'],
            ['Vencendo', $ven, 'amber'],
            ['Crítico', $cr, 'red']
        ] as $b):
            $pct = round($b[1] / $total * 100);
        ?>
            <div class="bar-row">
                <div>
                    <b><?= $b[0] ?></b>
                    <span><?= $pct ?>%</span>
                </div>
                <div class="bar">
                    <i class="<?= $b[2] ?>" style="width: <?= $pct ?>%"></i>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</div>

<section class="panel">
    <h2>Funcionários por setor</h2>
    <div class="sector-grid">
        <?php
        $set = $pdo->query("SELECT setor, COUNT(*) n FROM funcionarios GROUP BY setor")->fetchAll();
        foreach ($set as $s):
        ?>
            <div>
                <b><?= htmlspecialchars($s['setor']) ?></b>
                <strong><?= $s['n'] ?></strong>
                <div class="sector-bar">
                    <i style="height: <?= min(100, $s['n'] * 12) ?>%"></i>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require 'includes/footer.php'; ?>