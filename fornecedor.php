<?php

$titulo = 'Controle de Fornecedor';
require_once __DIR__ . '/config/config.php';
require 'includes/header.php';

// Reutiliza as mesmas credenciais e o mesmo banco configurados em config/config.php.
$DB_HOST = $host;
$DB_USER = $user;
$DB_PASS = $pass;
$DB_NAME = $db;
$DB_PORT = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
    $mysqli->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('Erro MySQL em fornecedor.php: ' . $e->getMessage());
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados. Verifique a configuração da hospedagem.');
}

// ======================================================
// CRIAÇÃO DAS TABELAS
// ======================================================

$mysqli->query("
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
");

$mysqli->query("
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
");

$mysqli->query("
CREATE TABLE IF NOT EXISTS supplier_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    product_id INT NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (supplier_id)
        REFERENCES suppliers(id)
        ON DELETE CASCADE,

    FOREIGN KEY (product_id)
        REFERENCES products(id)
        ON DELETE CASCADE
) ENGINE=InnoDB
");


// ======================================================
// FUNÇÃO PARA PEGAR POST
// ======================================================

function p($key)
{
    return isset($_POST[$key])
        ? trim($_POST[$key])
        : '';
}

// ======================================================
// AÇÕES
// ======================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ==================================================
    // CRIAR FORNECEDOR
    // ==================================================

    if (isset($_POST['create_supplier'])) {

        $name = p('name');
        $contact = p('contact');

        if ($name !== '') {

            $stmt = $mysqli->prepare("
                INSERT INTO suppliers (name, contact)
                VALUES (?, ?)
            ");

            $stmt->bind_param(
                "ss",
                $name,
                $contact
            );

            $stmt->execute();
            $stmt->close();
        }

        header("Location: fornecedor.php");
        exit;
    }

    // ==================================================
    // EDITAR FORNECEDOR
    // ==================================================

    if (isset($_POST['edit_supplier'])) {

        $id = (int)p('id');
        $name = p('name');
        $contact = p('contact');

        if ($id > 0 && $name !== '') {

            $stmt = $mysqli->prepare("
                UPDATE suppliers
                SET name = ?, contact = ?
                WHERE id = ?
            ");

            $stmt->bind_param(
                "ssi",
                $name,
                $contact,
                $id
            );

            $stmt->execute();
            $stmt->close();
        }

        header("Location: fornecedor.php");
        exit;
    }

    // ==================================================
    // EXCLUIR FORNECEDOR
    // ==================================================

    if (isset($_POST['delete_supplier'])) {

        $id = (int)p('id');

        if ($id > 0) {

            $stmt = $mysqli->prepare("
                DELETE FROM suppliers
                WHERE id = ?
            ");

            $stmt->bind_param(
                "i",
                $id
            );

            $stmt->execute();
            $stmt->close();
        }

        header("Location: fornecedor.php");
        exit;
    }

    // ==================================================
    // SALVAR / ATUALIZAR PREÇO
    // ==================================================

    if (isset($_POST['save_price'])) {

        $supplier_id = (int)p('supplier_id');
        $product_name = p('product_name');

        $price = str_replace(
            ',',
            '.',
            p('price')
        );

        $price = (float)$price;

        if (
            $supplier_id > 0 &&
            $product_name !== '' &&
            $price > 0
        ) {

            // Procurar produto
            $stmt = $mysqli->prepare("
                SELECT id
                FROM products
                WHERE name = ?
                LIMIT 1
            ");

            $stmt->bind_param(
                "s",
                $product_name
            );

            $stmt->execute();
            $stmt->bind_result($product_id);

            if (!$stmt->fetch()) {
                $product_id = 0;
            }

            $stmt->close();

            // Criar produto se não existir
            if ($product_id === 0) {

                $stmt = $mysqli->prepare("
                    INSERT INTO products (name)
                    VALUES (?)
                ");

                $stmt->bind_param(
                    "s",
                    $product_name
                );

                $stmt->execute();

                $product_id = $stmt->insert_id;

                $stmt->close();
            }

            // Procurar preço
            $stmt = $mysqli->prepare("
                SELECT id
                FROM supplier_prices
                WHERE supplier_id = ?
                AND product_id = ?
                LIMIT 1
            ");

            $stmt->bind_param(
                "ii",
                $supplier_id,
                $product_id
            );

            $stmt->execute();
            $stmt->bind_result($price_id);

            if (!$stmt->fetch()) {
                $price_id = 0;
            }

            $stmt->close();

            // Atualizar preço
            if ($price_id > 0) {

                $stmt = $mysqli->prepare("
                    UPDATE supplier_prices
                    SET price = ?
                    WHERE id = ?
                ");

                $stmt->bind_param(
                    "di",
                    $price,
                    $price_id
                );

                $stmt->execute();
                $stmt->close();

            } else {

                // Inserir preço
                $stmt = $mysqli->prepare("
                    INSERT INTO supplier_prices
                    (
                        supplier_id,
                        product_id,
                        price
                    )
                    VALUES (?, ?, ?)
                ");

                $stmt->bind_param(
                    "iid",
                    $supplier_id,
                    $product_id,
                    $price
                );

                $stmt->execute();
                $stmt->close();
            }
        }

        header(
            "Location: fornecedor.php?manage_prices=" .
            $supplier_id
        );

        exit;
    }

    // ==================================================
    // EXCLUIR PREÇO
    // ==================================================

    if (isset($_POST['delete_price'])) {

        $id = (int)p('id');
        $supplier_id = (int)p('supplier_id');

        if ($id > 0) {

            $stmt = $mysqli->prepare("
                DELETE FROM supplier_prices
                WHERE id = ?
            ");

            $stmt->bind_param(
                "i",
                $id
            );

            $stmt->execute();
            $stmt->close();
        }

        header(
            "Location: fornecedor.php?manage_prices=" .
            $supplier_id
        );

        exit;
    }
}

// ======================================================
// BUSCAR FORNECEDORES
// ======================================================

$suppliers = $mysqli->query("
    SELECT *
    FROM suppliers
    ORDER BY name ASC
");

$totalSuppliers = $suppliers->num_rows;

// ======================================================
// BUSCAR PRODUTOS
// ======================================================

$products = $mysqli->query("
    SELECT *
    FROM products
    ORDER BY name ASC
");

$totalProducts = $products->num_rows;

// ======================================================
// BUSCAR PREÇOS
// ======================================================

$prices = $mysqli->query("
    SELECT
        sp.id,
        s.name AS supplier_name,
        p.name AS product_name,
        sp.price,
        sp.supplier_id,
        sp.product_id

    FROM supplier_prices sp

    INNER JOIN suppliers s
        ON s.id = sp.supplier_id

    INNER JOIN products p
        ON p.id = sp.product_id

    ORDER BY
        p.name ASC,
        sp.price ASC
");

$totalPrices = $prices->num_rows;

// ======================================================
// EDITAR FORNECEDOR
// ======================================================

$editSupplier = null;

if (isset($_GET['edit_supplier'])) {

    $id = (int)$_GET['edit_supplier'];

    if ($id > 0) {

        $stmt = $mysqli->prepare("
            SELECT id, name, contact
            FROM suppliers
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->bind_param(
            "i",
            $id
        );

        $stmt->execute();

        $stmt->bind_result(
            $sid,
            $sname,
            $scontact
        );

        if ($stmt->fetch()) {

            $editSupplier = [
                'id' => $sid,
                'name' => $sname,
                'contact' => $scontact
            ];
        }

        $stmt->close();
    }
}

// ======================================================
// GERENCIAR PREÇOS
// ======================================================

$manageSupplierId = isset($_GET['manage_prices'])
    ? (int)$_GET['manage_prices']
    : 0;

$manageSupplier = null;
$manageSupplierPrices = [];

if ($manageSupplierId > 0) {

    $stmt = $mysqli->prepare("
        SELECT id, name
        FROM suppliers
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->bind_param(
        "i",
        $manageSupplierId
    );

    $stmt->execute();

    $stmt->bind_result(
        $msid,
        $msname
    );

    if ($stmt->fetch()) {

        $manageSupplier = [
            'id' => $msid,
            'name' => $msname
        ];
    }

    $stmt->close();

    if ($manageSupplier !== null) {

        $stmt = $mysqli->prepare("
            SELECT
                sp.id,
                p.name AS product_name,
                sp.price,
                p.id AS product_id

            FROM supplier_prices sp

            INNER JOIN products p
                ON p.id = sp.product_id

            WHERE sp.supplier_id = ?

            ORDER BY p.name ASC
        ");

        $stmt->bind_param(
            "i",
            $manageSupplierId
        );

        $stmt->execute();

        $result = $stmt->get_result();

        while (
            $row = $result->fetch_assoc()
        ) {

            $manageSupplierPrices[] = $row;
        }

        $stmt->close();
    }
}

// ======================================================
// COMPARAR PREÇOS
// ======================================================

$compareResults = [];
$compareQuery = '';

if (
    isset($_GET['compare']) &&
    trim($_GET['compare']) !== ''
) {

    $compareQuery = trim($_GET['compare']);

    $stmt = $mysqli->prepare("
        SELECT
            p.name AS product_name,
            s.name AS supplier_name,
            sp.price

        FROM supplier_prices sp

        INNER JOIN products p
            ON p.id = sp.product_id

        INNER JOIN suppliers s
            ON s.id = sp.supplier_id

        WHERE p.name LIKE ?

        ORDER BY
            p.name ASC,
            sp.price ASC
    ");

    $like = "%" . $compareQuery . "%";

    $stmt->bind_param(
        "s",
        $like
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while (
        $row = $result->fetch_assoc()
    ) {

        $compareResults[] = $row;
    }

    $stmt->close();
}

?>

<style>
/* ===== SafeFlow - visual moderno da página de fornecedores ===== */
:root{
    --sf-navy:#0f172a;
    --sf-blue:#2563eb;
    --sf-blue-dark:#1d4ed8;
    --sf-slate:#64748b;
    --sf-bg:#f1f5f9;
    --sf-card:#ffffff;
    --sf-border:#e2e8f0;
    --sf-success:#16a34a;
    --sf-danger:#dc2626;
    --sf-radius:18px;
    --sf-shadow:0 10px 30px rgba(15,23,42,.07);
}

body{background:var(--sf-bg);}

.sf-container{
    width:min(1400px, calc(100% - 40px));
    margin:0 auto;
    padding:28px 0 50px;
}

.sf-hero{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    margin-bottom:24px;
}

.sf-eyebrow{
    margin:0 0 6px;
    color:var(--sf-blue);
    font-size:12px;
    font-weight:800;
    letter-spacing:.1em;
    text-transform:uppercase;
}

.sf-hero h1{
    margin:0;
    color:var(--sf-navy);
    font-size:clamp(26px,3vw,38px);
    line-height:1.1;
}

.sf-hero p{
    margin:8px 0 0;
    color:var(--sf-slate);
    font-size:15px;
}

.sf-stats{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:16px;
    margin-bottom:24px;
}

.sf-stat{
    position:relative;
    overflow:hidden;
    padding:22px;
    background:var(--sf-card);
    border:1px solid var(--sf-border);
    border-radius:var(--sf-radius);
    box-shadow:var(--sf-shadow);
}

.sf-stat::after{
    content:"";
    position:absolute;
    width:70px;
    height:70px;
    right:-25px;
    top:-25px;
    border-radius:50%;
    background:#dbeafe;
}

.sf-stat span{
    display:block;
    color:var(--sf-slate);
    font-size:13px;
    font-weight:700;
}

.sf-stat strong{
    display:block;
    margin-top:8px;
    color:var(--sf-navy);
    font-size:32px;
    line-height:1;
}

.sf-panel{
    margin-bottom:24px;
    background:var(--sf-card);
    border:1px solid var(--sf-border);
    border-radius:var(--sf-radius);
    box-shadow:var(--sf-shadow);
    overflow:hidden;
}

.sf-panel-header{
    padding:22px 24px;
    border-bottom:1px solid var(--sf-border);
}

.sf-panel-header h2{
    margin:0;
    color:var(--sf-navy);
    font-size:20px;
}

.sf-panel-header p{
    margin:5px 0 0;
    color:var(--sf-slate);
    font-size:14px;
}

.sf-panel-body{padding:24px;}

.sf-grid{
    display:grid;
    grid-template-columns:minmax(0,1.25fr) minmax(300px,.75fr);
    gap:24px;
}

.sf-column-title{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin:0 0 14px;
    color:#334155;
    font-size:14px;
    font-weight:800;
}

.sf-list{
    display:grid;
    gap:10px;
    max-height:520px;
    overflow:auto;
    padding-right:4px;
}

.sf-supplier{
    display:grid;
    grid-template-columns:minmax(0,1fr) auto;
    gap:14px;
    align-items:center;
    padding:16px;
    border:1px solid var(--sf-border);
    border-radius:14px;
    background:#f8fafc;
    transition:.2s ease;
}

.sf-supplier:hover{
    transform:translateY(-1px);
    border-color:#bfdbfe;
    background:#fff;
    box-shadow:0 8px 20px rgba(15,23,42,.06);
}

.sf-supplier-name{
    color:var(--sf-navy);
    font-weight:800;
    font-size:15px;
}

.sf-contact{
    margin-top:5px;
    color:var(--sf-slate);
    font-size:13px;
}

.sf-actions{
    display:flex;
    flex-wrap:wrap;
    justify-content:flex-end;
    gap:7px;
}

.sf-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:38px;
    padding:0 13px;
    border:0;
    border-radius:10px;
    font:inherit;
    font-size:13px;
    font-weight:800;
    text-decoration:none;
    cursor:pointer;
    transition:.2s ease;
}

.sf-btn:hover{transform:translateY(-1px);}
.sf-btn-primary{background:var(--sf-blue);color:#fff;}
.sf-btn-primary:hover{background:var(--sf-blue-dark);}
.sf-btn-secondary{background:#e2e8f0;color:#334155;}
.sf-btn-secondary:hover{background:#cbd5e1;}
.sf-btn-danger{background:#fee2e2;color:#b91c1c;}
.sf-btn-danger:hover{background:#fecaca;}

.sf-form{
    padding:20px;
    background:#f8fafc;
    border:1px solid var(--sf-border);
    border-radius:16px;
}

.sf-field{display:block;margin-bottom:15px;}
.sf-field:last-of-type{margin-bottom:18px;}

.sf-field span{
    display:block;
    margin-bottom:7px;
    color:#334155;
    font-size:13px;
    font-weight:800;
}

.sf-field input{
    width:100%;
    height:44px;
    padding:0 13px;
    box-sizing:border-box;
    border:1px solid #cbd5e1;
    border-radius:10px;
    background:#fff;
    color:var(--sf-navy);
    font:inherit;
    outline:none;
    transition:.2s ease;
}

.sf-field input:focus{
    border-color:#60a5fa;
    box-shadow:0 0 0 4px rgba(37,99,235,.1);
}

.sf-form-actions{display:flex;gap:8px;flex-wrap:wrap;}

.sf-manage{
    margin-top:0;
    padding:20px;
    background:#f8fafc;
    border:1px solid var(--sf-border);
    border-radius:16px;
}

.sf-manage-title{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-bottom:18px;
}

.sf-manage-title h3{
    margin:0;
    color:var(--sf-navy);
    font-size:16px;
}

.sf-badge{
    display:inline-flex;
    align-items:center;
    min-height:24px;
    padding:0 9px;
    border-radius:999px;
    background:#dcfce7;
    color:#166534;
    font-size:11px;
    font-weight:900;
}

.sf-table-wrap{
    overflow:auto;
    border:1px solid var(--sf-border);
    border-radius:14px;
    background:#fff;
}

.sf-table{
    width:100%;
    border-collapse:collapse;
    min-width:500px;
}

.sf-table th{
    padding:12px 15px;
    background:#f8fafc;
    color:#64748b;
    font-size:11px;
    letter-spacing:.05em;
    text-transform:uppercase;
    text-align:left;
    border-bottom:1px solid var(--sf-border);
}

.sf-table td{
    padding:14px 15px;
    color:#334155;
    font-size:14px;
    border-bottom:1px solid #f1f5f9;
}

.sf-table tr:last-child td{border-bottom:0;}
.sf-table tr:hover td{background:#f8fafc;}

.sf-price{
    color:var(--sf-success);
    font-weight:900;
    white-space:nowrap;
}

.sf-search{
    display:flex;
    gap:10px;
    padding:18px;
    background:#f8fafc;
    border:1px solid var(--sf-border);
    border-radius:14px;
}

.sf-search input{
    flex:1;
    min-width:0;
    height:44px;
    padding:0 14px;
    border:1px solid #cbd5e1;
    border-radius:10px;
    background:#fff;
    font:inherit;
    outline:none;
}

.sf-search input:focus{
    border-color:#60a5fa;
    box-shadow:0 0 0 4px rgba(37,99,235,.1);
}

.sf-product{
    margin-top:16px;
    border:1px solid var(--sf-border);
    border-radius:15px;
    overflow:hidden;
}

.sf-product-title{
    display:flex;
    align-items:center;
    gap:10px;
    padding:14px 16px;
    background:#f8fafc;
    color:var(--sf-navy);
    font-weight:900;
}

.sf-cheap td{background:#f0fdf4;}
.sf-cheap:hover td{background:#dcfce7 !important;}

.sf-empty{
    padding:28px 18px;
    text-align:center;
    color:var(--sf-slate);
    background:#f8fafc;
    border:1px dashed #cbd5e1;
    border-radius:14px;
    font-size:14px;
}

.sf-divider{
    height:1px;
    margin:22px 0;
    background:var(--sf-border);
}

@media (max-width:900px){
    .sf-grid{grid-template-columns:1fr;}
    .sf-stats{grid-template-columns:1fr;}
}

@media (max-width:640px){
    .sf-container{width:min(100% - 24px,1400px);padding-top:18px;}
    .sf-hero{align-items:flex-start;flex-direction:column;}
    .sf-panel-body{padding:16px;}
    .sf-panel-header{padding:18px;}
    .sf-supplier{grid-template-columns:1fr;}
    .sf-actions{justify-content:flex-start;}
    .sf-search{flex-direction:column;}
    .sf-search .sf-btn{width:100%;}
}
</style>

<div class="sf-container">

    <div class="page-head">
        <div>
            <span class="eyebrow">FORNECEDORES</span>
            <h1>Controle de fornecedores</h1>
        </div>
        <a class="btn btn-primary" href="dashboard.php">← Voltar</a>
    </div>

    <div class="cards three">
        <div class="metric">
            <span>Fornecedores cadastrados</span>
            <b><?= $totalSuppliers ?></b>
        </div>
        <div class="metric">
            <span>Produtos cadastrados</span>
            <b><?= $totalProducts ?></b>
        </div>
        <div class="metric">
            <span>Preços cadastrados</span>
            <b><?= $totalPrices ?></b>
        </div>
    </div>

    <section class="sf-panel">
        <div class="sf-panel-header">
            <h2>Fornecedores</h2>
            <p>Cadastre e organize seus fornecedores.</p>
        </div>

        <div class="sf-panel-body">
            <div class="sf-grid">

                <div>
                    <h3 class="sf-column-title">Lista de fornecedores</h3>

                    <div class="sf-list">
                        <?php if ($suppliers->num_rows > 0): ?>
                            <?php while ($s = $suppliers->fetch_assoc()): ?>
                                <article class="sf-supplier">
                                    <div>
                                        <div class="sf-supplier-name">
                                            <?= htmlspecialchars($s['name']) ?>
                                        </div>

                                        <?php if (!empty($s['contact'])): ?>
                                            <div class="sf-contact">
                                                <?= htmlspecialchars($s['contact']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="sf-actions">
                                        <a href="fornecedor.php?manage_prices=<?= (int)$s['id'] ?>"
                                           class="sf-btn sf-btn-secondary">Preços</a>

                                        <a href="fornecedor.php?edit_supplier=<?= (int)$s['id'] ?>"
                                           class="sf-btn sf-btn-secondary">Editar</a>

                                        <form method="post" action="fornecedor.php"
                                              onsubmit="return confirm('Deseja realmente excluir este fornecedor?');">
                                            <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                            <button type="submit" name="delete_supplier"
                                                    class="sf-btn sf-btn-danger">Excluir</button>
                                        </form>
                                    </div>
                                </article>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="sf-empty">Nenhum fornecedor cadastrado.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <h3 class="sf-column-title">
                        <?= $editSupplier !== null ? 'Editar fornecedor' : 'Novo fornecedor' ?>
                    </h3>

                    <form method="post" action="fornecedor.php" class="sf-form">
                        <?php if ($editSupplier !== null): ?>
                            <input type="hidden" name="id" value="<?= (int)$editSupplier['id'] ?>">
                        <?php endif; ?>

                        <label class="sf-field">
                            <span>Nome do fornecedor</span>
                            <input type="text" name="name" required
                                   value="<?= htmlspecialchars($editSupplier['name'] ?? '') ?>"
                                   placeholder="Ex.: Empresa ABC">
                        </label>

                        <label class="sf-field">
                            <span>Contato</span>
                            <input type="text" name="contact"
                                   value="<?= htmlspecialchars($editSupplier['contact'] ?? '') ?>"
                                   placeholder="Telefone ou e-mail">
                        </label>

                        <div class="sf-form-actions">
                            <?php if ($editSupplier !== null): ?>
                                <button type="submit" name="edit_supplier"
                                        class="sf-btn sf-btn-primary">Salvar alterações</button>
                                <a href="fornecedor.php" class="sf-btn sf-btn-secondary">Cancelar</a>
                            <?php else: ?>
                                <button type="submit" name="create_supplier"
                                        class="sf-btn sf-btn-primary">+ Cadastrar fornecedor</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </section>

    <section class="sf-panel">
        <div class="sf-panel-header">
            <h2>Gerenciar preços</h2>
            <p>Cadastre e atualize os preços dos produtos por fornecedor.</p>
        </div>

        <div class="sf-panel-body">
            <?php if ($manageSupplier !== null): ?>

                <div class="sf-manage">
                    <div class="sf-manage-title">
                        <h3><?= htmlspecialchars($manageSupplier['name'] ?? '') ?></h3>
                        <span class="sf-badge">Fornecedor selecionado</span>
                    </div>

                    <div class="sf-grid">
                        <div>
                            <h3 class="sf-column-title">Preços cadastrados</h3>

                            <?php if (!empty($manageSupplierPrices)): ?>
                                <div class="sf-table-wrap">
                                    <table class="sf-table">
                                        <thead>
                                            <tr>
                                                <th>Produto</th>
                                                <th>Preço</th>
                                                <th>Ação</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($manageSupplierPrices as $mp): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($mp['product_name']) ?></strong></td>
                                                    <td>
                                                        <span class="sf-price">
                                                            R$ <?= number_format((float)$mp['price'], 2, ',', '.') ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <form method="post" action="fornecedor.php"
                                                              onsubmit="return confirm('Excluir este preço?');">
                                                            <input type="hidden" name="id" value="<?= (int)$mp['id'] ?>">
                                                            <input type="hidden" name="supplier_id" value="<?= (int)$manageSupplierId ?>">
                                                            <button type="submit" name="delete_price"
                                                                    class="sf-btn sf-btn-danger">Excluir</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="sf-empty">Nenhum preço cadastrado para este fornecedor.</div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <h3 class="sf-column-title">Novo preço</h3>

                            <form method="post" action="fornecedor.php" class="sf-form">
                                <input type="hidden" name="supplier_id" value="<?= (int)$manageSupplierId ?>">

                                <label class="sf-field">
                                    <span>Produto</span>
                                    <input type="text" name="product_name" required
                                           placeholder="Nome do produto">
                                </label>

                                <label class="sf-field">
                                    <span>Preço</span>
                                    <input type="text" name="price" required
                                           inputmode="decimal" placeholder="0,00">
                                </label>

                                <button type="submit" name="save_price"
                                        class="sf-btn sf-btn-primary">+ Adicionar preço</button>
                            </form>
                        </div>
                    </div>

                    <div style="margin-top:16px;">
                        <a href="dashboard.php" class="sf-btn sf-btn-secondary">← Voltar</a>
                    </div>
                </div>

            <?php else: ?>
                <div class="sf-empty">
                    Selecione um fornecedor na lista acima para gerenciar seus preços.
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="sf-panel">
        <div class="sf-panel-header">
            <h2>Comparar preços</h2>
            <p>Pesquise um produto e veja qual fornecedor oferece o menor valor.</p>
        </div>

        <div class="sf-panel-body">
            <form method="get" action="fornecedor.php" class="sf-search">
                <input type="text" name="compare"
                       placeholder="Digite o nome do produto..."
                       value="<?= htmlspecialchars($compareQuery) ?>">
                <button type="submit" class="sf-btn sf-btn-primary">Pesquisar</button>
            </form>

            <?php if (!empty($compareResults)): ?>

                <?php
                $grouped = [];
                foreach ($compareResults as $r) {
                    $grouped[$r['product_name']][] = $r;
                }
                ?>

                <?php foreach ($grouped as $pname => $rows): ?>
                    <?php $min = min(array_column($rows, 'price')); ?>

                    <div class="sf-product">
                        <div class="sf-product-title">
                            <?= htmlspecialchars($pname) ?>
                        </div>

                        <div class="sf-table-wrap" style="border:0;border-radius:0;">
                            <table class="sf-table">
                                <thead>
                                    <tr>
                                        <th>Fornecedor</th>
                                        <th>Preço</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $r): ?>
                                        <?php $isCheapest = (float)$r['price'] === (float)$min; ?>

                                        <tr class="<?= $isCheapest ? 'sf-cheap' : '' ?>">
                                            <td>
                                                <strong><?= htmlspecialchars($r['supplier_name']) ?></strong>

                                                <?php if ($isCheapest): ?>
                                                    <span class="sf-badge" style="margin-left:8px;">
                                                        MENOR PREÇO
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <span class="sf-price">
                                                    R$ <?= number_format((float)$r['price'], 2, ',', '.') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php elseif (isset($_GET['compare'])): ?>

                <div class="sf-empty" style="margin-top:16px;">
                    Nenhum produto encontrado para
                    <strong>"<?= htmlspecialchars($compareQuery) ?>"</strong>.
                </div>

            <?php endif; ?>
        </div>
    </section>

    <section class="sf-panel">
        <div class="sf-panel-header">
            <h2>Todos os preços</h2>
            <p>Visão geral de todos os preços cadastrados.</p>
        </div>

        <div class="sf-panel-body">
            <?php if ($prices->num_rows > 0): ?>

                <div class="sf-table-wrap">
                    <table class="sf-table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Fornecedor</th>
                                <th>Preço</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($r = $prices->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($r['product_name']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($r['supplier_name']) ?></td>
                                    <td>
                                        <span class="sf-price">
                                            R$ <?= number_format((float)$r['price'], 2, ',', '.') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <div class="sf-empty">Nenhum preço cadastrado.</div>
            <?php endif; ?>
        </div>
    </section>

</div>

<script src="js/modals.js"></script>

<?php require 'includes/footer.php'; ?>
