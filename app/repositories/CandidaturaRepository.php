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

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado ? Candidatura::arrayParaObjeto($resultado) : null;
    }

    public function buscarPorVagaETrabalhador(int $idVaga, int $idTrabalhador): ?Candidatura
    {
        $sql = "SELECT *
                FROM candidatura
                WHERE id_vaga = :vaga
                AND id_trabalhador = :trabalhador";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':vaga', $idVaga, PDO::PARAM_INT);
        $stmt->bindValue(':trabalhador', $idTrabalhador, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($resultado)) {
            return Candidatura::arrayParaObjeto($resultado[0]);
        }

        return null;
    }

    public function listarContatosAceitos(int $idVaga): array
    {
        $sql = "
            SELECT
                u.nome,
                u.telefone
            FROM candidatura i
            INNER JOIN usuario u
                ON u.id_usuario = i.id_trabalhador
            WHERE i.id_vaga = :vaga
              AND i.status = 'ACEITO'
            ORDER BY u.nome
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':vaga', $idVaga, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function criar(Candidatura $candidatura): int
    {
        $sql = "INSERT INTO candidatura (id_vaga, id_trabalhador, status, data_candidatura)
                VALUES (:vaga, :trabalhador, :status, :data)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':vaga', $candidatura->getIdVaga(), PDO::PARAM_INT);
        $stmt->bindValue(':trabalhador', $candidatura->getIdTrabalhador(), PDO::PARAM_INT);
        $stmt->bindValue(':status', $candidatura->getStatus(), PDO::PARAM_STR);
        $stmt->bindValue(':data', $candidatura->getDataInteresse(), PDO::PARAM_STR);
        $stmt->execute();

        $id = (int)$this->conn->lastInsertId();

        return $id;
    }

    public function listarPorVaga(int $idVaga): array
    {
        $sql = "SELECT i.*, u.nome AS nome_trabalhador, u.telefone AS telefone_trabalhador
                FROM candidatura i
                INNER JOIN usuario u ON u.id_usuario = i.id_trabalhador
                WHERE i.id_vaga = :vaga
                ORDER BY i.data_candidatura ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':vaga', $idVaga, PDO::PARAM_INT);
        $stmt->execute();

        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn($row) => Candidatura::arrayParaObjeto($row),
            $dados
        );
    }

    public function listarAceitos(int $idVaga): array
    {
        $sql = "SELECT *
                FROM candidatura
                WHERE id_vaga = :vaga
                AND status = 'ACEITO'
                ORDER BY data_candidatura";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':vaga', $idVaga, PDO::PARAM_INT);
        $stmt->execute();

        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn($row) => Candidatura::arrayParaObjeto($row),
            $dados
        );
    }

    public function contarAceitos(int $idVaga): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM candidatura
                WHERE id_vaga = :vaga
                AND status = 'ACEITO'";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':vaga', $idVaga, PDO::PARAM_INT);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public function aceitar(int $idInteresse): bool
    {
        $sql = "UPDATE candidatura
                SET status = 'ACEITO'
                WHERE id_candidatura = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $idInteresse, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function remover(int $idInteresse): bool
    {
        $sql = "DELETE
                FROM candidatura
                WHERE id_candidatura = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $idInteresse, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function listarPorTrabalhador(int $idTrabalhador): array
    {
        $sql = "SELECT *
                FROM candidatura
                WHERE id_trabalhador = :trabalhador
                ORDER BY data_candidatura DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':trabalhador', $idTrabalhador, PDO::PARAM_INT);
        $stmt->execute();

        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn($row) => Candidatura::arrayParaObjeto($row),
            $dados
        );
    }
}