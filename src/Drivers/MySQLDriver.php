<?php

namespace Fflch\Gwdb\Drivers;

use PDO;

class MySQLDriver implements DriverInterface
{
    private PDO $pdo;

    private const DATABASES_IGNORADAS = [
        'information_schema',
        'mysql',
        'performance_schema',
        'sys',
        'm_ysql',
        '_m_ysql',
    ];

    private const USUARIOS_IGNORADOS = [
        'root',
        'mysql',
        'mariadb.sys',
        'debian-sys-maint',
        'mysql.session',
        'mysql.sys',
        'healthcheck',
        'admin',
    ];

    public function __construct(string $host, string $port, string $user, string $pass)
    {
        $this->pdo = new PDO(
            "mysql:host=$host;port=$port",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public function listarDatabases(): array
    {
        $lista = "'" . implode("','", self::DATABASES_IGNORADAS) . "'";
        $stmt = $this->pdo->query("
            SHOW DATABASES
            WHERE `Database` NOT IN ($lista)
        ");

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function listarUsuarios(): array
    {
        $lista = "'" . implode("','", self::USUARIOS_IGNORADOS) . "'";
        $stmt = $this->pdo->query("
            SELECT User
            FROM mysql.user
            WHERE User NOT IN ($lista)
            AND User <> ''
            GROUP BY User
            ORDER BY User
        ");

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function criarDatabase(string $nome): void
    {
        $this->pdo->exec("CREATE DATABASE `$nome`");
    }

    public function criarUsuario(string $nome, string $senha): void
    {
        $this->pdo->exec("CREATE USER `$nome`@'%' IDENTIFIED BY '$senha'");
    }

    public function concederPrivilegios(string $nome): void
    {
        $this->pdo->exec("GRANT ALL PRIVILEGES ON `$nome`.* TO `$nome`@'%'");
        $this->pdo->exec("FLUSH PRIVILEGES");
    }

    public function trocarSenha(string $nome, string $senha): void
    {
        $this->pdo->exec("ALTER USER `$nome`@'%' IDENTIFIED BY '$senha'");
    }
}