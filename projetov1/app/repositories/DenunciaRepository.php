<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Denuncia;
use PDO;

class DenunciaRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function buscarPorId(int $id): ?Denuncia
    {
        $sql = "SELECT * FROM denuncia WHERE id_denuncia = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado ? Denuncia::arrayParaObjeto($resultado) : null;
    }

    public function listarPendentes(): array
    {
        $sql = "SELECT * FROM denuncia WHERE status = 'PENDENTE' ORDER BY data DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        return array_map(fn($row) => Denuncia::arrayParaObjeto($row), $resultados);
    }

    public function listarTodas(): array
    {
        $sql = "SELECT * FROM denuncia ORDER BY data DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        return array_map(fn($row) => Denuncia::arrayParaObjeto($row), $resultados);
    }

    public function listarPorDenunciante(int $idDenunciante): array
    {
        $sql = "SELECT * FROM denuncia WHERE id_denunciante = :id_denunciante ORDER BY data DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_denunciante', $idDenunciante, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        return array_map(fn($row) => Denuncia::arrayParaObjeto($row), $resultados);
    }

    public function listarPorDenunciado(int $idDenunciado): array
    {
        $sql = "SELECT * FROM denuncia WHERE id_denunciado = :id_denunciado ORDER BY data DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_denunciado', $idDenunciado, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        return array_map(fn($row) => Denuncia::arrayParaObjeto($row), $resultados);
    }

    public function criar(Denuncia $denuncia): int
    {
        $sql = "INSERT INTO denuncia (id_denunciante, id_denunciado, id_anuncio, motivo, descricao, status) 
                VALUES (:id_denunciante, :id_denunciado, :id_anuncio, :motivo, :descricao, :status)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_denunciante', $denuncia->getIdDenunciante(), PDO::PARAM_INT);
        $stmt->bindValue(':id_denunciado', $denuncia->getIdDenunciado(), PDO::PARAM_INT);
        $stmt->bindValue(':id_anuncio', $denuncia->getIdAnuncio(), PDO::PARAM_INT);
        $stmt->bindValue(':motivo', $denuncia->getMotivo(), PDO::PARAM_STR);
        $stmt->bindValue(':descricao', $denuncia->getDescricao(), PDO::PARAM_STR);
        $stmt->bindValue(':status', $denuncia->getStatus(), PDO::PARAM_STR);
        
        $stmt->execute();
        return (int)$this->conn->lastInsertId();
    }

    public function atualizar(Denuncia $denuncia): bool
    {
        $sql = "UPDATE denuncia 
                SET id_denunciante = :id_denunciante, id_denunciado = :id_denunciado, 
                    id_anuncio = :id_anuncio, motivo = :motivo, descricao = :descricao, status = :status 
                WHERE id_denuncia = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $denuncia->getIdDenuncia(), PDO::PARAM_INT);
        $stmt->bindValue(':id_denunciante', $denuncia->getIdDenunciante(), PDO::PARAM_INT);
        $stmt->bindValue(':id_denunciado', $denuncia->getIdDenunciado(), PDO::PARAM_INT);
        $stmt->bindValue(':id_anuncio', $denuncia->getIdAnuncio(), PDO::PARAM_INT);
        $stmt->bindValue(':motivo', $denuncia->getMotivo(), PDO::PARAM_STR);
        $stmt->bindValue(':descricao', $denuncia->getDescricao(), PDO::PARAM_STR);
        $stmt->bindValue(':status', $denuncia->getStatus(), PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    public function mudarStatus(int $idDenuncia, string $status): bool
    {
        $sql = "UPDATE denuncia SET status = :status WHERE id_denuncia = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $idDenuncia, PDO::PARAM_INT);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function contarDenunciasAoPorUsuario(int $idUsuario): int
    {
        $sql = "SELECT COUNT(*) as total FROM denuncia WHERE id_denunciado = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return (int)$resultado['total'];
    }
}
