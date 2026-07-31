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

    public function buscarPorVagaDeTrabalhador(int $idVaga, int $idTrabalhador): ?Candidatura
    {
        $sql = "SELECT * FROM candidatura WHERE id_vaga = :id_vaga AND id_trabalhador = :id_trabalhador";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_vaga', $idVaga, PDO::PARAM_INT);
        $stmt->bindValue(':id_trabalhador', $idTrabalhador, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado ? Candidatura::arrayParaObjeto($resultado) : null;
    }

    public function listarPorVaga(int $idVaga): array
    {
        $sql = "SELECT * FROM candidatura WHERE id_vaga = :id_vaga ORDER BY data_candidatura DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_vaga', $idVaga, PDO::PARAM_INT);
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
        $sql = "INSERT INTO candidatura (id_vaga, id_trabalhador, status) 
                VALUES (:id_vaga, :id_trabalhador, :status)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_vaga', $candidatura->getIdVaga(), PDO::PARAM_INT);
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

    public function buscarAceitosPorVaga(int $idVaga): array
    {
        $sql = "SELECT * FROM candidatura WHERE id_vaga = :id_vaga AND status = 'ACEITO'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_vaga', $idVaga, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        return array_map(fn($row) => Candidatura::arrayParaObjeto($row), $resultados);
    }

    public function contarCandidatos(int $idVaga): int
    {
        $sql = "SELECT COUNT(*) as total FROM candidatura WHERE id_vaga = :id_vaga";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_vaga', $idVaga, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return (int)$resultado['total'];
    }
}
