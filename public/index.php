<?php

require __DIR__ . '/../vendor/autoload.php';

use Fflch\Gwdb\Database;

// ... validacao do X-Token continua igual ...

$input = json_decode(file_get_contents('php://input'), true) ?? [];

// "driver" e opcional: se nao vier, usa DB_DRIVER_PADRAO do .env
Database::usar($input['driver'] ?? null);

$action = $input['action'] ?? null;
$nome = $input['nome'] ?? null;

$resposta = match ($action) {
    'listar_databases' => Database::listar_databases(),
    'listar_usuarios' => Database::listar_usuarios(),
    'database_existe' => json_encode(['existe' => Database::database_existe($nome)]),
    'usuario_existe' => json_encode(['existe' => Database::usuario_existe($nome)]),
    'criar_database' => Database::criar_database($nome),
    'criar_usuario' => Database::criar_usuario($nome),
    'criar_database_usuario' => Database::criar_database_usuario($nome),
    'conceder_privilegios' => Database::conceder_privilegios($nome),
    'criar_database_usuario_privilegio' => Database::criar_database_usuario_privilegio($nome),
    'trocar_senha' => Database::trocar_senha($nome),
    default => json_encode(['sucesso' => false, 'mensagem' => 'Action invalida']),
};

header('Content-Type: application/json');
echo $resposta;