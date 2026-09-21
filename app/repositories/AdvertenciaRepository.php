<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Advertencia;
use PDO;

class AdvertenciaRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function criar(int $idUsuario, ?int $idDenuncia, ?int $idModerador, string $mensagem): int
    {
        $sql = "INSERT INTO advertencia (id_usuario, id_denuncia, id_moderador, mensagem)
                VALUES (:id_usuario, :id_denuncia, :id_moderador, :mensagem)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':id_denuncia', $idDenuncia, $idDenuncia === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':id_moderador', $idModerador, $idModerador === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':mensagem', $mensagem, PDO::PARAM_STR);
        $stmt->execute();

        return (int)$this->conn->lastInsertId();
    }

    /**
     * Advertências ainda não dispensadas pelo usuário, da mais recente para a mais antiga.
     */
    public function listarNaoVisualizadas(int $idUsuario): array
    {
        $sql = "SELECT * FROM advertencia
                WHERE id_usuario = :id_usuario AND visualizada_em IS NULL
                ORDER BY data_advertencia DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(fn($row) => Advertencia::arrayParaObjeto($row), $stmt->fetchAll());
    }

    /**
     * Marca como visualizada. O filtro por id_usuario impede dispensar advertência alheia.
     */
    public function marcarVisualizada(int $idAdvertencia, int $idUsuario): bool
    {
        $sql = "UPDATE advertencia SET visualizada_em = NOW()
                WHERE id_advertencia = :id AND id_usuario = :id_usuario AND visualizada_em IS NULL";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $idAdvertencia, PDO::PARAM_INT);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}
