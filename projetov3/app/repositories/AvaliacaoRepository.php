<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Avaliacao;
use PDO;

class AvaliacaoRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function buscarPorId(int $id): ?Avaliacao
    {
        $sql = "SELECT * FROM avaliacao WHERE id_avaliacao = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado ? Avaliacao::arrayParaObjeto($resultado) : null;
    }

    public function buscarPorAvaliadorAvaliadoAnuncio(int $idAvaliador, int $idAvaliado, int $idAnuncio): ?Avaliacao
    {
        $sql = "SELECT * FROM avaliacao 
                WHERE id_avaliador = :id_avaliador 
                AND id_avaliado = :id_avaliado 
                AND id_anuncio = :id_anuncio";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_avaliador', $idAvaliador, PDO::PARAM_INT);
        $stmt->bindValue(':id_avaliado', $idAvaliado, PDO::PARAM_INT);
        $stmt->bindValue(':id_anuncio', $idAnuncio, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado ? Avaliacao::arrayParaObjeto($resultado) : null;
    }

    public function listarPorAvaliado(int $idAvaliado): array
    {
        $sql = "SELECT * FROM avaliacao WHERE id_avaliado = :id_avaliado ORDER BY data DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_avaliado', $idAvaliado, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        return array_map(fn($row) => Avaliacao::arrayParaObjeto($row), $resultados);
    }

    public function listarPorAnuncio(int $idAnuncio): array
    {
        $sql = "SELECT * FROM avaliacao WHERE id_anuncio = :id_anuncio ORDER BY data DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_anuncio', $idAnuncio, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        return array_map(fn($row) => Avaliacao::arrayParaObjeto($row), $resultados);
    }

    public function criar(Avaliacao $avaliacao): int
    {
        $sql = "INSERT INTO avaliacao (id_avaliador, id_avaliado, id_anuncio, nota, comentario) 
                VALUES (:id_avaliador, :id_avaliado, :id_anuncio, :nota, :comentario)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_avaliador', $avaliacao->getIdAvaliador(), PDO::PARAM_INT);
        $stmt->bindValue(':id_avaliado', $avaliacao->getIdAvaliado(), PDO::PARAM_INT);
        $stmt->bindValue(':id_anuncio', $avaliacao->getIdAnuncio(), PDO::PARAM_INT);
        $stmt->bindValue(':nota', $avaliacao->getNota(), PDO::PARAM_INT);
        $stmt->bindValue(':comentario', $avaliacao->getComentario(), PDO::PARAM_STR);
        
        $stmt->execute();
        return (int)$this->conn->lastInsertId();
    }

    public function atualizar(Avaliacao $avaliacao): bool
    {
        $sql = "UPDATE avaliacao 
                SET nota = :nota, comentario = :comentario 
                WHERE id_avaliacao = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $avaliacao->getIdAvaliacao(), PDO::PARAM_INT);
        $stmt->bindValue(':nota', $avaliacao->getNota(), PDO::PARAM_INT);
        $stmt->bindValue(':comentario', $avaliacao->getComentario(), PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    public function calcularMediaNotas(int $idAvaliado): float
    {
        $sql = "SELECT AVG(nota) as media FROM avaliacao WHERE id_avaliado = :id_avaliado";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_avaliado', $idAvaliado, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return round($resultado['media'] ?? 0, 2);
    }

    public function contarAvaliacoes(int $idAvaliado): int
    {
        $sql = "SELECT COUNT(*) as total FROM avaliacao WHERE id_avaliado = :id_avaliado";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_avaliado', $idAvaliado, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return (int)$resultado['total'];
    }
}
