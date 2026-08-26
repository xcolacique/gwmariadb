<?php

namespace Fflch\Gwdb;

use Fflch\Gwdb\Drivers\DriverInterface;
use Fflch\Gwdb\Drivers\MySQLDriver;
use Fflch\Gwdb\Drivers\PostgresDriver;
use PDOException;

class Database
{
    /** @var array<string, DriverInterface> cache de instancias, uma por tipo de driver */
    private static array $drivers = [];

    /** Tipo escolhido explicitamente para a requisicao atual (ver usar()) */
    private static ?string $tipoAtual = null;

    /**
     * Define qual driver usar nas chamadas seguintes desta requisicao.
     * Chame no bootstrap (index.php) antes de despachar a action, com o
     * valor que veio no corpo do JSON, se houver.
     *
     * @param bool $persistirNaSessao Se true, grava a escolha em $_SESSION
     *   para que requisicoes futuras (com o mesmo cookie) nao precisem
     *   informar "driver" de novo. Exige session_start() ja chamado.
     */
    public static function usar(?string $tipo, bool $persistirNaSessao = false): void
    {
        self::$tipoAtual = $tipo ?: null;

        if ($persistirNaSessao && self::$tipoAtual && session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['db_driver'] = self::$tipoAtual;
        }
    }

    private static function driver(): DriverInterface
    {
        $tipoNaSessao = session_status() === PHP_SESSION_ACTIVE
            ? ($_SESSION['db_driver'] ?? null)
            : null;

        $tipo = self::$tipoAtual
            ?? $tipoNaSessao
            ?? $_ENV['DB_DRIVER_PADRAO'] ?? $_SERVER['DB_DRIVER_PADRAO']
            ?? 'mysql';

        // normaliza apelidos para uma chave unica de cache
        $chave = match ($tipo) {
            'pgsql', 'postgres', 'postgresql' => 'pgsql',
            'mysql', 'mariadb' => 'mysql',
            default => throw new \RuntimeException("Driver '$tipo' nao suportado"),
        };

        if (isset(self::$drivers[$chave])) {
            return self::$drivers[$chave];
        }

        self::$drivers[$chave] = match ($chave) {
            'pgsql' => new PostgresDriver(
                $_ENV['DB_PGSQL_HOST'] ?? $_SERVER['DB_PGSQL_HOST'] ?? '127.0.0.1',
                $_ENV['DB_PGSQL_PORT'] ?? $_SERVER['DB_PGSQL_PORT'] ?? '5432',
                $_ENV['DB_PGSQL_USER'] ?? $_SERVER['DB_PGSQL_USER'] ?? 'postgres',
                $_ENV['DB_PGSQL_PASS'] ?? $_SERVER['DB_PGSQL_PASS'] ?? '',
                $_ENV['DB_PGSQL_ADMIN_NAME'] ?? $_SERVER['DB_PGSQL_ADMIN_NAME'] ?? 'postgres'
            ),
            'mysql' => new MySQLDriver(
                $_ENV['DB_MYSQL_HOST'] ?? $_SERVER['DB_MYSQL_HOST'] ?? '127.0.0.1',
                $_ENV['DB_MYSQL_PORT'] ?? $_SERVER['DB_MYSQL_PORT'] ?? '3306',
                $_ENV['DB_MYSQL_USER'] ?? $_SERVER['DB_MYSQL_USER'] ?? 'root',
                $_ENV['DB_MYSQL_PASS'] ?? $_SERVER['DB_MYSQL_PASS'] ?? ''
            ),
        };

        return self::$drivers[$chave];
    }

    public static function listar_databases(): string
    {
        try {
            return json_encode(self::driver()->listarDatabases());
        } catch (PDOException $e) {
            return json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public static function listar_usuarios(): string
    {
        try {
            return json_encode(self::driver()->listarUsuarios());
        } catch (PDOException $e) {
            return json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public static function database_existe(string $nome): bool
    {
        $lista = json_decode(self::listar_databases(), true);
        return in_array($nome, $lista ?? []);
    }

    public static function usuario_existe(string $nome): bool
    {
        $lista = json_decode(self::listar_usuarios(), true);
        return in_array($nome, $lista ?? []);
    }

    public static function criar_database(string $nome): string
    {
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $nome)) {
            return json_encode(['sucesso' => false, 'mensagem' => 'Nome invalido']);
        } elseif (strlen($nome) > 64) {
            return json_encode(['sucesso' => false, 'mensagem' => 'Nome muito grande']);
        }

        if (self::database_existe($nome)) {
            return json_encode(['sucesso' => false, 'mensagem' => 'Database ja existe']);
        }

        if (self::usuario_existe($nome)) {
            return json_encode(['sucesso' => false, 'mensagem' => 'Usuario com este nome ja existe']);
        }

        try {
            self::driver()->criarDatabase($nome);
            return json_encode(['sucesso' => true, 'mensagem' => 'Database criada com sucesso']);
        } catch (PDOException $e) {
            return json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public static function criar_usuario(string $nome): string
    {
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $nome)) {
            return json_encode(['sucesso' => false, 'mensagem' => 'Nome invalido']);
        } elseif (strlen($nome) > 32) {
            return json_encode(['sucesso' => false, 'mensagem' => 'Nome muito grande']);
        }

        if (self::usuario_existe($nome)) {
            return json_encode(['sucesso' => false, 'mensagem' => 'Usuario ja existe']);
        }

        $senha = self::gerar_senha();

        try {
            self::driver()->criarUsuario($nome, $senha);
            return json_encode(['sucesso' => true, 'mensagem' => 'Usuario criado com sucesso', 'senha' => $senha]);
        } catch (PDOException $e) {
            return json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    private static function gerar_senha(int $tamanho = 24): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $senha = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $tamanho; $i++) {
            $senha .= $chars[random_int(0, $max)];
        }
        return $senha;
    }

    public static function criar_database_usuario(string $nome): string
    {
        $criar_database = json_decode(self::criar_database($nome), true);
        if (!$criar_database['sucesso']) {
            return json_encode(['sucesso' => false, 'mensagem' => $criar_database['mensagem']]);
        }

        $criar_usuario = json_decode(self::criar_usuario($nome), true);
        if (!$criar_usuario['sucesso']) {
            return json_encode(['sucesso' => false, 'mensagem' => $criar_usuario['mensagem']]);
        }

        return json_encode(['sucesso' => true, 'senha' => $criar_usuario['senha']]);
    }

    public static function conceder_privilegios(string $nome): string
    {
        if (!self::database_existe($nome)) {
            return json_encode(['sucesso' => false, 'mensagem' => 'Database nao existe']);
        }

        if (!self::usuario_existe($nome)) {
            return json_encode(['sucesso' => false, 'mensagem' => 'Usuario nao existe']);
        }

        try {
            self::driver()->concederPrivilegios($nome);
            return json_encode(['sucesso' => true, 'mensagem' => 'Privilegios concedidos com sucesso']);
        } catch (PDOException $e) {
            return json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public static function criar_database_usuario_privilegio(string $nome): string
    {
        $criar_database_usuario = json_decode(self::criar_database_usuario($nome), true);
        if (!$criar_database_usuario['sucesso']) {
            return json_encode(['sucesso' => false, 'mensagem' => $criar_database_usuario['mensagem']]);
        }

        $conceder_privilegios = json_decode(self::conceder_privilegios($nome), true);
        if (!$conceder_privilegios['sucesso']) {
            return json_encode(['sucesso' => false, 'mensagem' => $conceder_privilegios['mensagem']]);
        }

        return json_encode(['sucesso' => true, 'senha' => $criar_database_usuario['senha']]);
    }

    public static function trocar_senha(string $nome): string
    {
        if (!self::usuario_existe($nome)) {
            return json_encode(['sucesso' => false, 'mensagem' => 'Usuario nao existe']);
        }

        $senha = self::gerar_senha();

        try {
            self::driver()->trocarSenha($nome, $senha);
            return json_encode(['sucesso' => true, 'senha' => $senha]);
        } catch (PDOException $e) {
            return json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }
}