<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Usuario;
use PDO;

class UsuarioRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    /**
     * Buscar usuário por ID
     */
    public function buscarPorId(int $id): ?Usuario
    {
        $sql = "SELECT * FROM usuario WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch();
        return $resultado ? Usuario::arrayParaObjeto($resultado) : null;
    }

    /**
     * Buscar usuário por email
     */
    public function buscarPorEmail(string $email): ?Usuario
    {
        $sql = "SELECT * FROM usuario WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $resultado = $stmt->fetch();
        return $resultado ? Usuario::arrayParaObjeto($resultado) : null;
    }

    /**
     * Buscar usuário ativo por email
     */
    public function buscarAtivoPorEmail(string $email): ?Usuario
    {
        $sql = "SELECT * FROM usuario WHERE email = :email AND ativo = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $resultado = $stmt->fetch();
        return $resultado ? Usuario::arrayParaObjeto($resultado) : null;
    }

    /**
     * Listar todos os usuários
     */
    public function listar(): array
    {
        $sql = "SELECT * FROM usuario ORDER BY data_cadastro DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $resultados = $stmt->fetchAll();
        return array_map(fn($row) => Usuario::arrayParaObjeto($row), $resultados);
    }

    /**
     * Listar usuários ativos por tipo
     */
    public function listarPorTipo(string $tipo): array
    {
        $sql = "SELECT * FROM usuario WHERE tipo_usuario = :tipo AND ativo = 1 ORDER BY data_cadastro DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $stmt->execute();

        $resultados = $stmt->fetchAll();
        return array_map(fn($row) => Usuario::arrayParaObjeto($row), $resultados);
    }

    /**
     * Criar usuário
     */
    public function criar(Usuario $usuario): int
    {
        $sql = "INSERT INTO usuario (nome, email, senha, telefone, tipo_usuario, foto_perfil, descricao, documento, ativo) 
                VALUES (:nome, :email, :senha, :telefone, :tipo_usuario, :foto_perfil, :descricao, :documento, :ativo)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nome', $usuario->getNome(), PDO::PARAM_STR);
        $stmt->bindValue(':email', $usuario->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':senha', $usuario->getSenha(), PDO::PARAM_STR);
        $stmt->bindValue(':telefone', $usuario->getTelefone(), PDO::PARAM_STR);
        $stmt->bindValue(':tipo_usuario', $usuario->getTipoUsuario(), PDO::PARAM_STR);
        $stmt->bindValue(':foto_perfil', $usuario->getFotoPerfil(), PDO::PARAM_STR);
        $stmt->bindValue(':descricao', $usuario->getDescricao(), PDO::PARAM_STR);
        $stmt->bindValue(':documento', $usuario->getDocumento(), PDO::PARAM_STR);
        $stmt->bindValue(':ativo', $usuario->getAtivo(), PDO::PARAM_INT);
        
        $stmt->execute();
        return (int)$this->conn->lastInsertId();
    }

    /**
     * Atualizar usuário
     */
    public function atualizar(Usuario $usuario): bool
    {
        $sql = "UPDATE usuario 
                SET nome = :nome, email = :email, senha = :senha, telefone = :telefone, 
                    tipo_usuario = :tipo_usuario, foto_perfil = :foto_perfil, 
                    descricao = :descricao, documento = :documento, ativo = :ativo
                WHERE id_usuario = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $usuario->getIdUsuario(), PDO::PARAM_INT);
        $stmt->bindValue(':nome', $usuario->getNome(), PDO::PARAM_STR);
        $stmt->bindValue(':email', $usuario->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':senha', $usuario->getSenha(), PDO::PARAM_STR);
        $stmt->bindValue(':telefone', $usuario->getTelefone(), PDO::PARAM_STR);
        $stmt->bindValue(':tipo_usuario', $usuario->getTipoUsuario(), PDO::PARAM_STR);
        $stmt->bindValue(':foto_perfil', $usuario->getFotoPerfil(), PDO::PARAM_STR);
        $stmt->bindValue(':descricao', $usuario->getDescricao(), PDO::PARAM_STR);
        $stmt->bindValue(':documento', $usuario->getDocumento(), PDO::PARAM_STR);
        $stmt->bindValue(':ativo', $usuario->getAtivo(), PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Bloquear usuário (soft delete)
     */
    public function bloquear(int $idUsuario): bool
    {
        $sql = "UPDATE usuario SET ativo = 0 WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $idUsuario, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Desbloquear usuário
     */
    public function desbloquear(int $idUsuario): bool
    {
        $sql = "UPDATE usuario SET ativo = 1 WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $idUsuario, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Atualizar foto de perfil
     */
    public function atualizarFotoPerfil(int $idUsuario, string $caminhoFoto): bool
    {
        $sql = "UPDATE usuario SET foto_perfil = :foto WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':foto', $caminhoFoto, PDO::PARAM_STR);
        return $stmt->execute();
    }

    /**
     * Atualizar senha
     */
    public function atualizarSenha(int $idUsuario, string $novaSenha): bool
    {
        $sql = "UPDATE usuario SET senha = :senha WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':senha', $novaSenha, PDO::PARAM_STR);
        return $stmt->execute();
    }

    /**
     * Verificar se email já existe
     */
    public function emailExiste(string $email, ?int $idExcluir = null): bool
    {
        if ($idExcluir !== null) {
            $sql = "SELECT COUNT(*) as count FROM usuario WHERE email = :email AND id_usuario != :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':id', $idExcluir, PDO::PARAM_INT);
        } else {
            $sql = "SELECT COUNT(*) as count FROM usuario WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado['count'] > 0;
    }

    /**
     * Adicionar habilidade a um trabalhador
     */
    public function adicionarHabilidade(int $idUsuario, int $idHabilidade): bool
    {
        $sql = "INSERT INTO usuario_habilidade (id_usuario, id_habilidade) VALUES (:id_usuario, :id_habilidade)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':id_habilidade', $idHabilidade, PDO::PARAM_INT);
        
        try {
            return $stmt->execute();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Remover habilidade de um trabalhador
     */
    public function removerHabilidade(int $idUsuario, int $idHabilidade): bool
    {
        $sql = "DELETE FROM usuario_habilidade WHERE id_usuario = :id_usuario AND id_habilidade = :id_habilidade";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':id_habilidade', $idHabilidade, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Buscar habilidades de um usuário
     */
    public function buscarHabilidades(int $idUsuario): array
    {
        $sql = "SELECT h.* FROM habilidade h 
                INNER JOIN usuario_habilidade uh ON h.id_habilidade = uh.id_habilidade 
                WHERE uh.id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        $resultados = $stmt->fetchAll();
        return $resultados ?: [];
    }

    /**
     * Limpar todas as habilidades de um usuário
     */
    public function limparHabilidades(int $idUsuario): bool
    {
        $sql = "DELETE FROM usuario_habilidade WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Métodos legados (mantidos para compatibilidade)
    public function getUsuarios(): array
    {
        return $this->listar();
    }

    public function getUsuarioById(int $id): ?Usuario
    {
        return $this->buscarPorId($id);
    }

    public function getUsuarioByEmail(string $email): ?Usuario
    {
        return $this->buscarPorEmail($email);
    }

    public function saveUsuario(Usuario $usuario): bool
    {
        $id = $this->criar($usuario);
        return $id > 0;
    }

    public function updateUsuario(Usuario $usuario): bool
    {
        return $this->atualizar($usuario);
    }

    public function deleteUsuario(int $id): bool
    {
        return $this->bloquear($id);
    }

    public function buscarContratantePorVaga(int $idVaga): ?object
    {
        $sql = "SELECT u.* FROM usuario u
                INNER JOIN vaga v ON u.id_usuario = v.id_contratante
                WHERE v.id_vaga = :id_vaga";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_vaga', $idVaga, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch();
        return $resultado ? Usuario::arrayParaObjeto($resultado) : null;
    }
}
