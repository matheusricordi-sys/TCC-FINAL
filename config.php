
<?php

$host = 'sql104.infinityfree.com';
$db   = 'if0_43344822_safeflow';
$user = 'if0_43344822';
$pass = 'ISABELLEM';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    die('Erro ao conectar ao banco de dados.');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
