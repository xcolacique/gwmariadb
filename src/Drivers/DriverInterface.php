<?php

namespace Fflch\Gwdb\Drivers;

interface DriverInterface
{
    /** @return string[] */
    public function listarDatabases(): array;

    /** @return string[] */
    public function listarUsuarios(): array;

    public function criarDatabase(string $nome): void;

    public function criarUsuario(string $nome, string $senha): void;

    public function concederPrivilegios(string $nome): void;

    public function trocarSenha(string $nome, string $senha): void;
}