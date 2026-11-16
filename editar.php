<?php

require_once __DIR__ . '/config/config.php';

// ======================================================
// EXIGIR LOGIN
// ======================================================

if (empty($_SESSION['usuario']['id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int) $_SESSION['usuario']['id'];

$errors = [];


// ======================================================
// GARANTIR COLUNAS NO BANCO
// ======================================================

$cols = $pdo->query("
    SELECT COLUMN_NAME
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'usuarios'
")->fetchAll(PDO::FETCH_COLUMN);


// Coluna avatar

if (!in_array('avatar', $cols, true)) {

    $pdo->exec("
        ALTER TABLE usuarios
        ADD COLUMN avatar VARCHAR(255) NULL
    ");

}


// Coluna telefone

if (!in_array('phone', $cols, true)) {

    $pdo->exec("
        ALTER TABLE usuarios
        ADD COLUMN phone VARCHAR(50) NULL
    ");

}


// Atualizar lista de colunas

$cols = $pdo->query("
    SELECT COLUMN_NAME
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'usuarios'
")->fetchAll(PDO::FETCH_COLUMN);


// ======================================================
// DESCOBRIR CAMPO DO NOME
// ======================================================

$nameField = null;

if (in_array('nome', $cols, true)) {

    $nameField = 'nome';

} elseif (in_array('name', $cols, true)) {

    $nameField = 'name';

}


// ======================================================
// BUSCAR USUÁRIO
// ======================================================

$stmt = $pdo->prepare("
    SELECT *
    FROM usuarios
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$userId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$user) {

    session_destroy();

    header('Location: login.php');

    exit;
}


// ======================================================
// PROCESSAR FORMULÁRIO
// ======================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    $name = trim($_POST['name'] ?? '');

    $phone = trim($_POST['phone'] ?? '');


    // ==================================================
    // VALIDAR E-MAIL
    // ==================================================

    if ($email === '') {

        $errors[] = 'O e-mail é obrigatório.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = 'Digite um e-mail válido.';

    }


    // ==================================================
    // UPLOAD DA FOTO
    // ==================================================

    $avatarPath = null;

    $oldAvatar = $user['avatar'] ?? '';


    if (
        isset($_FILES['avatar']) &&
        $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $file = $_FILES['avatar'];


        // Verificar erro do upload

        if ($file['error'] !== UPLOAD_ERR_OK) {

            switch ($file['error']) {

                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:

                    $errors[] =
                        'A imagem é muito grande.';

                    break;

                case UPLOAD_ERR_PARTIAL:

                    $errors[] =
                        'O upload da imagem foi interrompido.';

                    break;

                default:

                    $errors[] =
                        'Erro ao enviar a imagem.';
            }

        }


        // Verificar tamanho

        if (
            empty($errors) &&
            $file['size'] > 2 * 1024 * 1024
        ) {

            $errors[] =
                'A imagem deve ter no máximo 2 MB.';
        }


        // Verificar se é realmente uma imagem

        if (empty($errors)) {

            $imageInfo =
                @getimagesize($file['tmp_name']);


            if ($imageInfo === false) {

                $errors[] =
                    'O arquivo enviado não é uma imagem válida.';
            }

        }


        // Verificar MIME

        if (empty($errors)) {

            $finfo =
                new finfo(FILEINFO_MIME_TYPE);

            $mime =
                $finfo->file($file['tmp_name']);


            $allowedTypes = [

                'image/jpeg' => 'jpg',

                'image/png' => 'png',

                'image/webp' => 'webp'

            ];


            if (!isset($allowedTypes[$mime])) {

                $errors[] =
                    'Formato inválido. Use JPG, PNG ou WEBP.';

            }

        }


        // ==================================================
        // SALVAR ARQUIVO
        // ==================================================

        if (empty($errors)) {

            $extension =
                $allowedTypes[$mime];


            /*
             * IMPORTANTE:
             * Esta pasta ficará dentro do projeto:
             *
             * C:\xampp\htdocs\epi\uploads\avatars
             */

            $uploadDir =
                __DIR__ .
                DIRECTORY_SEPARATOR .
                'uploads' .
                DIRECTORY_SEPARATOR .
                'avatars';


            // Criar pasta se não existir

            if (!is_dir($uploadDir)) {

                if (!mkdir($uploadDir, 0775, true)) {

                    $errors[] =
                        'Não foi possível criar a pasta uploads/avatars.';
                }

            }


            // Verificar permissão

            if (
                empty($errors) &&
                !is_writable($uploadDir)
            ) {

                $errors[] =
                    'A pasta uploads/avatars não possui permissão para gravação.';

            }


            if (empty($errors)) {

                /*
                 * Nome único para evitar conflito
                 */

                $filename =
                    'avatar_' .
                    $userId .
                    '_' .
                    bin2hex(random_bytes(8)) .
                    '.' .
                    $extension;


                $destination =
                    $uploadDir .
                    DIRECTORY_SEPARATOR .
                    $filename;


                if (
                    !move_uploaded_file(
                        $file['tmp_name'],
                        $destination
                    )
                ) {

                    $errors[] =
                        'Não foi possível salvar a foto no servidor.';

                } else {

                    /*
                     * Caminho que será salvo no banco.
                     */

                    $avatarPath =
                        'uploads/avatars/' .
                        $filename;

                }

            }

        }

    }


    // ==================================================
    // SALVAR DADOS
    // ==================================================

    if (empty($errors)) {

        $sets = [];

        $params = [];


        // Nome

        if ($nameField !== null) {

            $sets[] =
                "`$nameField` = ?";

            $params[] =
                $name;

        }


        // E-mail

        $sets[] =
            "`email` = ?";

        $params[] =
            $email;


        // Telefone

        $sets[] =
            "`phone` = ?";

        $params[] =
            $phone;


        // Foto

        if ($avatarPath !== null) {

            $sets[] =
                "`avatar` = ?";

            $params[] =
                $avatarPath;

        }


        $params[] =
            $userId;


        $sql =
            "UPDATE usuarios SET " .
            implode(', ', $sets) .
            " WHERE id = ?";


        try {

            $stmt =
                $pdo->prepare($sql);

            $stmt->execute($params);


            // ==================================================
            // APAGAR FOTO ANTIGA
            // ==================================================

            if (
                $avatarPath !== null &&
                !empty($oldAvatar)
            ) {

                /*
                 * Só tenta apagar arquivos
                 * que estejam dentro da pasta de avatars.
                 */

                $oldFile =
                    __DIR__ .
                    DIRECTORY_SEPARATOR .
                    str_replace(
                        '/',
                        DIRECTORY_SEPARATOR,
                        $oldAvatar
                    );


                if (
                    is_file($oldFile) &&
                    strpos(
                        realpath($oldFile) ?: '',
                        realpath(
                            __DIR__ .
                            DIRECTORY_SEPARATOR .
                            'uploads' .
                            DIRECTORY_SEPARATOR .
                            'avatars'
                        ) ?: '---'
                    ) === 0
                ) {

                    @unlink($oldFile);

                }

            }


            // ==================================================
            // ATUALIZAR SESSÃO
            // ==================================================

            $stmt =
                $pdo->prepare("
                    SELECT *
                    FROM usuarios
                    WHERE id = ?
                    LIMIT 1
                ");

            $stmt->execute([$userId]);

            $updatedUser =
                $stmt->fetch(PDO::FETCH_ASSOC);


            if ($updatedUser) {

                $_SESSION['usuario'] =
                    $updatedUser;

            }


            // ==================================================
            // REDIRECIONAR
            // ==================================================

            header(
                'Location: editar.php?ok=1'
            );

            exit;


        } catch (PDOException $e) {

            // Se o banco falhar e uma foto nova
            // já tiver sido salva, apagar a foto.

            if (
                $avatarPath !== null
            ) {

                $newFile =
                    __DIR__ .
                    DIRECTORY_SEPARATOR .
                    str_replace(
                        '/',
                        DIRECTORY_SEPARATOR,
                        $avatarPath
                    );

                if (is_file($newFile)) {

                    @unlink($newFile);

                }

            }


            $errors[] =
                'Não foi possível atualizar o perfil.';
        }

    }


    // ==================================================
    // ATUALIZAR DADOS EXIBIDOS
    // ==================================================

    if (!empty($errors)) {

        $user['email'] =
            $email;

        $user['phone'] =
            $phone;

        if ($nameField !== null) {

            $user[$nameField] =
                $name;

        }

    }

}


// ======================================================
// DADOS PARA EXIBIÇÃO
// ======================================================

$userName = '';

if ($nameField !== null) {

    $userName =
        $user[$nameField] ?? '';

}

$userEmail =
    $user['email'] ?? '';

$userPhone =
    $user['phone'] ?? '';

$userAvatar =
    $user['avatar'] ?? '';

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Editar Perfil | SafeFlow</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f4f7fb;

    color: #172033;
}


/* HEADER */

.topbar {

    height: 78px;

    background: #ffffff;

    border-bottom:
        1px solid #e5e7eb;

    display: flex;

    align-items: center;

    padding: 10px 32px;

    box-shadow:
        0 2px 8px rgba(0,0,0,.03);
}


.brand-area {

    display: flex;

    flex-direction: column;

    gap: 2px;
}


.logo {

    font-size: 22px;

    font-weight: 800;

    color: #2563eb;
}


.logo span {

    color: #0f172a;
}


.back-button {

    text-decoration: none;

    color: #64748b;

    font-size: 12px;

    font-weight: 600;

    transition: .2s;
}


.back-button:hover {

    color: #2563eb;
}


/* CONTAINER */

.container {

    max-width: 900px;

    margin: 0 auto;

    padding: 35px 20px;
}


/* HEADER */

.page-header {

    margin-bottom: 22px;
}


.page-header h1 {

    margin: 0;

    font-size: 30px;

    color: #0f172a;
}


.page-header p {

    margin: 7px 0 0;

    color: #64748b;

    font-size: 14px;
}


/* CARD */

.card {

    background: #ffffff;

    border:
        1px solid #e5e7eb;

    border-radius: 16px;

    box-shadow:
        0 5px 20px
        rgba(15,23,42,.06);

    overflow: hidden;
}


.card-header {

    padding: 22px 25px;

    border-bottom:
        1px solid #edf0f4;
}


.card-header h2 {

    margin: 0;

    font-size: 18px;
}


.card-header p {

    margin: 5px 0 0;

    color: #64748b;

    font-size: 13px;
}


.card-body {

    padding: 25px;
}


/* GRID */

.profile-grid {

    display: grid;

    grid-template-columns: 180px 1fr;

    gap: 35px;

    align-items: start;
}


/* AVATAR */

.avatar-area {

    text-align: center;
}


.avatar {

    width: 140px;

    height: 140px;

    border-radius: 50%;

    object-fit: cover;

    border: 4px solid #ffffff;

    box-shadow:
        0 4px 15px
        rgba(15,23,42,.15);

    background: #eff6ff;
}


.avatar-placeholder {

    width: 140px;

    height: 140px;

    border-radius: 50%;

    background: #eff6ff;

    color: #2563eb;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 50px;

    margin: 0 auto;

    border: 4px solid #ffffff;

    box-shadow:
        0 4px 15px
        rgba(15,23,42,.12);
}


.file-input {

    width: 100%;

    margin-top: 12px;

    font-size: 12px;
}


.avatar-info {

    margin-top: 10px;

    color: #64748b;

    font-size: 11px;

    line-height: 1.5;
}


/* FORM */

.form-group {

    margin-bottom: 18px;
}


.form-label {

    display: block;

    margin-bottom: 7px;

    font-size: 13px;

    font-weight: 700;

    color: #334155;
}


.form-control {

    width: 100%;

    height: 44px;

    padding: 0 13px;

    border:
        1px solid #cbd5e1;

    border-radius: 9px;

    background: #ffffff;

    font-size: 14px;

    color: #0f172a;

    outline: none;
}


.form-control:focus {

    border-color: #2563eb;

    box-shadow:
        0 0 0 3px
        rgba(37,99,235,.10);
}


/* BUTTONS */

.buttons {

    display: flex;

    gap: 10px;

    margin-top: 22px;
}


.btn {

    border: 0;

    border-radius: 9px;

    padding: 11px 17px;

    font-size: 13px;

    font-weight: 700;

    cursor: pointer;

    text-decoration: none;

    display: inline-flex;

    align-items: center;

    justify-content: center;
}


.btn-primary {

    background: #2563eb;

    color: #ffffff;
}


.btn-primary:hover {

    background: #1d4ed8;
}


.btn-secondary {

    background: #f1f5f9;

    color: #475569;
}


.btn-secondary:hover {

    background: #e2e8f0;
}


/* MENSAGENS */

.message {

    border-radius: 9px;

    padding: 12px 14px;

    margin-bottom: 18px;

    font-size: 13px;
}


.error {

    background: #fef2f2;

    border:
        1px solid #fecaca;

    color: #b91c1c;
}


.success {

    background: #ecfdf5;

    border:
        1px solid #bbf7d0;

    color: #166534;
}


/* RESPONSIVO */

@media (max-width: 700px) {

    .topbar {

        padding: 10px 18px;
    }


    .container {

        padding: 22px 15px;
    }


    .profile-grid {

        grid-template-columns: 1fr;
    }


    .avatar-area {

        margin-bottom: 5px;
    }


    .card-body {

        padding: 20px;
    }


    .buttons {

        flex-direction: column;
    }


    .btn {

        width: 100%;
    }

}

</style>

</head>


<body>


<header class="topbar">

    <div class="brand-area">

        <div class="logo">

            Safe<span>Flow</span>

        </div>


        <a
            href="dashboard.php"
            class="back-button"
        >
            ← Voltar
        </a>

    </div>

</header>


<main class="container">


    <div class="page-header">

        <h1>
            Meu perfil
        </h1>

        <p>
            Atualize suas informações pessoais e sua foto.
        </p>

    </div>


    <div class="card">


        <div class="card-header">

            <h2>
                Editar perfil
            </h2>

            <p>
                Mantenha seus dados sempre atualizados.
            </p>

        </div>


        <div class="card-body">


            <?php if (!empty($_GET['ok'])): ?>

                <div class="message success">

                    ✓ Alterações salvas com sucesso.

                </div>

            <?php endif; ?>


            <?php if (!empty($errors)): ?>

                <?php foreach ($errors as $error): ?>

                    <div class="message error">

                        <?= htmlspecialchars(
                            $error,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>


            <form
                method="post"
                enctype="multipart/form-data"
                action="editar.php"
            >


                <div class="profile-grid">


                    <!-- FOTO -->

                    <div class="avatar-area">


                        <?php if (!empty($userAvatar)): ?>

                            <img
                                src="<?= htmlspecialchars(
                                    $userAvatar,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                class="avatar"
                                alt="Foto do perfil"
                            >

                        <?php else: ?>

                            <div class="avatar-placeholder">
                                👤
                            </div>

                        <?php endif; ?>


                        <input
                            type="file"
                            name="avatar"
                            class="file-input"
                            accept="image/jpeg,image/png,image/webp"
                        >


                        <div class="avatar-info">

                            JPG, PNG ou WEBP<br>

                            Tamanho máximo: 2 MB

                        </div>

                    </div>


                    <!-- DADOS -->

                    <div>


                        <?php if ($nameField !== null): ?>

                            <div class="form-group">

                                <label
                                    class="form-label"
                                    for="name"
                                >
                                    Nome
                                </label>


                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        $userName,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    placeholder="Digite seu nome"
                                >

                            </div>

                        <?php endif; ?>


                        <div class="form-group">

                            <label
                                class="form-label"
                                for="email"
                            >
                                E-mail
                            </label>


                            <input
                                id="email"
                                name="email"
                                type="email"
                                class="form-control"
                                required
                                value="<?= htmlspecialchars(
                                    $userEmail,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                placeholder="seu@email.com"
                            >

                        </div>


                        <div class="form-group">

                            <label
                                class="form-label"
                                for="phone"
                            >
                                Telefone
                            </label>


                            <input
                                id="phone"
                                name="phone"
                                type="text"
                                class="form-control"
                                value="<?= htmlspecialchars(
                                    $userPhone,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                placeholder="(00) 00000-0000"
                            >

                        </div>


                        <div class="buttons">


                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Salvar alterações
                            </button>


                            <a
                                href="dashboard.php"
                                class="btn btn-secondary"
                            >
                                Cancelar
                            </a>


                        </div>


                    </div>


                </div>


            </form>


        </div>

    </div>

</main>


</body>

</html>
