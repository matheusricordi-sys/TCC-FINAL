<?php

require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$page = basename($_SERVER['PHP_SELF']);

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        SafeFlow | <?= htmlspecialchars($titulo ?? 'Sistema') ?>
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>


    <!-- ==================================================
         MENU LATERAL
         ================================================== -->

    <aside class="sidebar">


        <!-- LOGO -->

        <a
            class="brand"
            href="dashboard.php"
        >

            <span>🛡</span>

            SafeFlow

        </a>


        <!-- MENU -->

        <nav>


            <a
                class="<?= $page === 'dashboard.php' ? 'active' : '' ?>"
                href="dashboard.php"
            >
                ▦
                <span>Dashboard</span>
            </a>


            <a
                class="<?= $page === 'funcionarios.php' ? 'active' : '' ?>"
                href="funcionarios.php"
            >
                ♙
                <span>Funcionários</span>
            </a>


            <a
                class="<?= $page === 'epi.php' ? 'active' : '' ?>"
                href="epi.php"
            >
                ⬡
                <span>Controle de EPI</span>
            </a>


            <a
                class="<?= $page === 'compras.php' ? 'active' : '' ?>"
                href="compras.php"
            >
                ▣
                <span>Compras</span>
            </a>


            <a
                class="<?= $page === 'documentos.php' ? 'active' : '' ?>"
                href="documentos.php"
            >
                ▤
                <span>Digitalização</span>
            </a>


            <a
                class="<?= $page === 'fornecedor.php' ? 'active' : '' ?>"
                href="fornecedor.php"
            >
                ⬡
                <span>Controle de Fornecedor</span>
            </a>


        </nav>


        <!-- ==================================================
             USUÁRIO
             ================================================== -->

        <a
            href="editar.php"
            class="sidebar-user"
            style="
                text-decoration:none;
                color:inherit;
                cursor:pointer;
            "
            title="Editar meu perfil"
        >

            <div class="avatar">
                MN
            </div>


            <div>

                <b>
                    <?= htmlspecialchars(
                        $_SESSION['usuario']['nome'] ?? 'Usuário'
                    ) ?>
                </b>


                <small>
                    <?= htmlspecialchars(
                        $_SESSION['usuario']['perfil'] ?? ''
                    ) ?>
                </small>

            </div>

        </a>


        <!-- SAIR -->

        <a
            class="logout"
            href="logout.php"
        >
            ↪ Sair
        </a>


    </aside>


    <!-- ==================================================
         CONTEÚDO PRINCIPAL
         ================================================== -->

    <main class="main">
