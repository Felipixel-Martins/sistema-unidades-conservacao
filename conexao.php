<?php

declare(strict_types=1);

$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'unidades_conservacao';

$conexao = mysqli_connect($host, $usuario, $senha, $banco);

if (!$conexao) {
    http_response_code(500);
    exit('Nao foi possivel conectar ao banco de dados.');
}

mysqli_set_charset($conexao, 'utf8mb4');
