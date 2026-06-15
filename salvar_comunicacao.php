<?php

declare(strict_types=1);

require __DIR__ . '/conexao.php';
require __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$titulo = trim($_POST['titulo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$email = trim($_POST['email'] ?? '');
$unidadeId = getIntParam($_POST, 'unidade_id');

if (
    $titulo === ''
    || $descricao === ''
    || $unidadeId === null
    || !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    redirect('detalhes.php?id=' . (int) $unidadeId . '&status=erro');
}

$sql = '
    INSERT INTO comunicacao (titulo, descricao, email, status, unidade_id)
    VALUES (?, ?, ?, 0, ?)
';

$stmt = mysqli_prepare($conexao, $sql);

if (!$stmt) {
    redirect('detalhes.php?id=' . $unidadeId . '&status=erro');
}

mysqli_stmt_bind_param($stmt, 'sssi', $titulo, $descricao, $email, $unidadeId);
$sucesso = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

redirect('detalhes.php?id=' . $unidadeId . ($sucesso ? '&status=ok' : '&status=erro'));
