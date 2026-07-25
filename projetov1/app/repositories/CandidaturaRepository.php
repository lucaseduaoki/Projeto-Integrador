<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Candidatura;
use PDO;

class CandidaturaRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function buscarPorId(int $id): ?Candidatura
    {
        $sql = "SELECT * FROM candidatura WHERE id_candidatura = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado ? Candidatura::arrayParaObjeto($resultado) : null;
    }

    public function buscarPorAnuncioDeTrabalhador(int $idAnuncio, int $idTrabalhador): ?Candidatura
    {
        $sql = "SELECT * FROM candidatura WHERE id_anuncio = :id_anuncio AND id_trabalhador = :id_trabalhador";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_anuncio', $idAnuncio, PDO::PARAM_INT);
        $stmt->bindValue(':id_trabalhador', $idTrabalhador, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado ? Candidatura::arrayParaObjeto($resultado) : null;
    }

    public function listarPorAnuncio(int $idAnuncio): array
    {
        $sql = "SELECT * FROM candidatura WHERE id_anuncio = :id_anuncio ORDER BY data_candidatura DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_anuncio', $idAnuncio, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        return array_map(fn($row) => Candidatura::arrayParaObjeto($row), $resultados);
    }

    public function listarPorTrabalhador(int $idTrabalhador): array
    {
        $sql = "SELECT * FROM candidatura WHERE id_trabalhador = :id_trabalhador ORDER BY data_candidatura DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_trabalhador', $idTrabalhador, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        return array_map(fn($row) => Candidatura::arrayParaObjeto($row), $resultados);
    }

    public function criar(Candidatura $candidatura): int
    {
        $sql = "INSERT INTO candidatura (id_anuncio, id_trabalhador, status) 
                VALUES (:id_anuncio, :id_trabalhador, :status)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_anuncio', $candidatura->getIdAnuncio(), PDO::PARAM_INT);
        $stmt->bindValue(':id_trabalhador', $candidatura->getIdTrabalhador(), PDO::PARAM_INT);
        $stmt->bindValue(':status', $candidatura->getStatus(), PDO::PARAM_STR);
        
        $stmt->execute();
        return (int)$this->conn->lastInsertId();
    }

    public function atualizar(Candidatura $candidatura): bool
    {
        $sql = "UPDATE candidatura 
                SET status = :status, data_selecao = :data_selecao 
                WHERE id_candidatura = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $candidatura->getIdCandidatura(), PDO::PARAM_INT);
        $stmt->bindValue(':status', $candidatura->getStatus(), PDO::PARAM_STR);
        $stmt->bindValue(':data_selecao', $candidatura->getDataSelecao(), PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    public function deletar(int $idCandidatura): bool
    {
        $sql = "DELETE FROM candidatura WHERE id_candidatura = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $idCandidatura, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function buscarAceitosPorAnuncio(int $idAnuncio): array
    {
        $sql = "SELECT * FROM candidatura WHERE id_anuncio = :id_anuncio AND status = 'ACEITO'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_anuncio', $idAnuncio, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        return array_map(fn($row) => Candidatura::arrayParaObjeto($row), $resultados);
    }

    public function contarCandidatos(int $idAnuncio): int
    {
        $sql = "SELECT COUNT(*) as total FROM candidatura WHERE id_anuncio = :id_anuncio";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_anuncio', $idAnuncio, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return (int)$resultado['total'];
    }
}
