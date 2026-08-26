<?php

namespace Fflch\Gwdb\Drivers;

use PDO;

class PostgresDriver implements DriverInterface
{
    private PDO $pdo;

    private const DATABASES_IGNORADAS = [
        'postgres',
        'template0',
        'template1',
    ];

    private const USUARIOS_IGNORADOS = [
        'postgres',
        'admin',
    ];

    public function __construct(string $host, string $port, string $user, string $pass, string $dbAdmin = 'postgres')
    {
   
        $this->pdo = new PDO(
            "pgsql:host=$host;port=$port;dbname=$dbAdmin",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public function listarDatabases(): array
    {
        $lista = "'" . implode("','", self::DATABASES_IGNORADAS) . "'";
        $stmt = $this->pdo->query("
            SELECT datname
            FROM pg_database
            WHERE datistemplate = false
            AND datname NOT IN ($lista)
            ORDER BY datname
        ");

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function listarUsuarios(): array
    {
        $lista = "'" . implode("','", self::USUARIOS_IGNORADOS) . "'";
        $stmt = $this->pdo->query("
            SELECT rolname
            FROM pg_roles
            WHERE rolcanlogin = true
            AND rolname NOT IN ($lista)
            AND rolname NOT LIKE 'pg\_%'
            ORDER BY rolname
        ");

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function criarDatabase(string $nome): void
    {
        $this->pdo->exec("CREATE DATABASE \"$nome\"");
    }

    public function criarUsuario(string $nome, string $senha): void
    {
        $this->pdo->exec("CREATE ROLE \"$nome\" WITH LOGIN PASSWORD '$senha'");
    }

    public function concederPrivilegios(string $nome): void
    {
               $this->pdo->exec("ALTER DATABASE \"$nome\" OWNER TO \"$nome\"");
    }

    public function trocarSenha(string $nome, string $senha): void
    {
        $this->pdo->exec("ALTER ROLE \"$nome\" WITH PASSWORD '$senha'");
    }
}