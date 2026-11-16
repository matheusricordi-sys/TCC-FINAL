<?php

require_once __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $n = 'PC-' . date('Y') . '-' . str_pad(
        (string)(($pdo->query("SELECT COUNT(*) n FROM pedidos")->fetch()['n']) + 1),
        3,
        '0',
        STR_PAD_LEFT
    );

    $st = $pdo->prepare(
        "INSERT INTO pedidos(numero, epi_id, fornecedor_id, quantidade, valor, data_pedido, prazo, status, observacoes) 
         VALUES(?, ?, ?, ?, ?, CURDATE(), ?, 'Aguardando', ?)"
    );

    $st->execute([
        $n,
        $_POST['epi_id'],
        $_POST['fornecedor_id'],
        $_POST['quantidade'],
        $_POST['valor'],
        $_POST['prazo'] ?: null,
        $_POST['observacoes'] ?? ''
    ]);
}

header('Location: compras.php');
exit;

?>