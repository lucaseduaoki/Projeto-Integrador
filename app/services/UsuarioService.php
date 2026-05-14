<?php

namespace app\services;

use app\models\Usuario;
use app\repositories\UsuarioRepository;
use Exception;

class UsuarioService
{
    private UsuarioRepository $repository;

    public function __construct()
    {
        $this->repository = new UsuarioRepository();
    }

    /**
     * Registrar novo usuário
     */
    public function registrar(
        string $nome,
        string $email,
        string $senha,
        string $tipoUsuario,
        ?string $telefone = null,
        ?string $documento = null
    ): Usuario {
        // Verificar se email já existe
        if ($this->repository->emailExiste($email)) {
            throw new Exception('Este e-mail já está cadastrado.');
        }

        // Hash da senha
        $senhaHash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);

        // Criar usuário
        $usuario = new Usuario(
            0,
            $nome,
            $email,
            $senhaHash,
            $tipoUsuario,
            $telefone,
            null,
            null,
            $documento,
            1
        );

        $idCriado = $this->repository->criar($usuario);
        $usuario->setIdUsuario($idCriado); // Atribuir ID ao objeto

        return $this->repository->buscarPorId($idCriado);
    }

    /**
     * Buscar usuário por ID
     */
    public function buscarPorId(int $id): ?Usuario
    {
        return $this->repository->buscarPorId($id);
    }

    /**
     * Buscar usuário por email
     */
    public function buscarPorEmail(string $email): ?Usuario
    {
        return $this->repository->buscarPorEmail($email);
    }

    /**
     * Listar usuários
     */
    public function listar(): array
    {
        return $this->repository->listar();
    }

    /**
     * Listar usuários por tipo
     */
    public function listarPorTipo(string $tipo): array
    {
        return $this->repository->listarPorTipo($tipo);
    }

    /**
     * Atualizar perfil do usuário
     */
    public function atualizarPerfil(Usuario $usuario): bool
    {
        // Verificar se email foi alterado e já existe
        if ($usuario->getEmail() !== $this->repository->buscarPorId($usuario->getIdUsuario())?->getEmail()) {
            if ($this->repository->emailExiste($usuario->getEmail(), $usuario->getIdUsuario())) {
                throw new Exception('Este e-mail já está cadastrado por outro usuário.');
            }
        }

        return $this->repository->atualizar($usuario);
    }

    /**
     * Atualizar senha
     */
    public function atualizarSenha(int $idUsuario, string $senhaAtual, string $novaSenha): bool
    {
        $usuario = $this->repository->buscarPorId($idUsuario);

        if (!$usuario) {
            throw new Exception('Usuário não encontrado.');
        }

        // Verificar senha atual
        if (!password_verify($senhaAtual, $usuario->getSenha())) {
            throw new Exception('Senha atual incorreta.');
        }

        $senhaHash = password_hash($novaSenha, PASSWORD_BCRYPT, ['cost' => 12]);
        return $this->repository->atualizarSenha($idUsuario, $senhaHash);
    }

    /**
     * Atualizar foto de perfil
     */
    public function atualizarFotoPerfil(int $idUsuario, string $caminhoFoto): bool
    {
        // Validar que arquivo existe
        if (!file_exists($caminhoFoto)) {
            throw new Exception('Arquivo de imagem não encontrado.');
        }

        return $this->repository->atualizarFotoPerfil($idUsuario, $caminhoFoto);
    }

    /**
     * Bloquear usuário
     */
    public function bloquear(int $idUsuario): bool
    {
        $usuario = $this->repository->buscarPorId($idUsuario);
        if (!$usuario) {
            throw new Exception('Usuário não encontrado.');
        }
        return $this->repository->bloquear($idUsuario);
    }

    /**
     * Desbloquear usuário
     */
    public function desbloquear(int $idUsuario): bool
    {
        $usuario = $this->repository->buscarPorId($idUsuario);
        if (!$usuario) {
            throw new Exception('Usuário não encontrado.');
        }
        return $this->repository->desbloquear($idUsuario);
    }

    /**
     * Adicionar habilidade a trabalhador
     */
    public function adicionarHabilidade(int $idUsuario, int $idHabilidade): bool
    {
        return $this->repository->adicionarHabilidade($idUsuario, $idHabilidade);
    }

    /**
     * Remover habilidade de trabalhador
     */
    public function removerHabilidade(int $idUsuario, int $idHabilidade): bool
    {
        return $this->repository->removerHabilidade($idUsuario, $idHabilidade);
    }

    /**
     * Buscar habilidades de usuário
     */
    public function buscarHabilidades(int $idUsuario): array
    {
        return $this->repository->buscarHabilidades($idUsuario);
    }

    /**
     * Limpar habilidades
     */
    public function limparHabilidades(int $idUsuario): bool
    {
        return $this->repository->limparHabilidades($idUsuario);
    }
}

