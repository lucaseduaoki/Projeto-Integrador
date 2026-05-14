<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Anuncio;
use PDO;

class AnuncioRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function buscarPorId(int $id): ?Anuncio
    {
        $sql = "SELECT * FROM anuncio WHERE id_anuncio = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado ? Anuncio::arrayParaObjeto($resultado) : null;
    }

    public function listar(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM anuncio WHERE status = 'ABERTO' ORDER BY data DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        return array_map(fn($row) => Anuncio::arrayParaObjeto($row), $resultados);
    }

    public function listarPorContratante(int $idContratante): array
    {
        $sql = "SELECT * FROM anuncio WHERE id_contratante = :id_contratante ORDER BY data DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_contratante', $idContratante, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        return array_map(fn($row) => Anuncio::arrayParaObjeto($row), $resultados);
    }

    public function buscar(string $titulo = '', string $localizacao = '', ?string $tipoServico = null): array
    {
        $sql = "SELECT * FROM anuncio WHERE status = 'ABERTO'";
        $parametros = [];

        if (!empty($titulo)) {
            $sql .= " AND (titulo LIKE :titulo OR descricao LIKE :titulo)";
            $parametros['titulo'] = "%$titulo%";
        }

        if (!empty($localizacao)) {
            $sql .= " AND localizacao LIKE :localizacao";
            $parametros['localizacao'] = "%$localizacao%";
        }

        if ($tipoServico !== null) {
            $sql .= " AND tipo_servico = :tipo_servico";
            $parametros['tipo_servico'] = $tipoServico;
        }

        $sql .= " ORDER BY data DESC";

        $stmt = $this->conn->prepare($sql);
        
        foreach ($parametros as $key => $valor) {
            $stmt->bindValue(':' . $key, $valor, PDO::PARAM_STR);
        }

        $stmt->execute();
        $resultados = $stmt->fetchAll();
        return array_map(fn($row) => Anuncio::arrayParaObjeto($row), $resultados);
    }

    public function criar(Anuncio $anuncio): int
    {
        $sql = "INSERT INTO anuncio (id_contratante, titulo, descricao, localizacao, data, remuneracao, 
                tipo_servico, duracao, observacoes, prazo_candidatura, status) 
                VALUES (:id_contratante, :titulo, :descricao, :localizacao, :data, :remuneracao, 
                :tipo_servico, :duracao, :observacoes, :prazo_candidatura, :status)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_contratante', $anuncio->getIdContratante(), PDO::PARAM_INT);
        $stmt->bindValue(':titulo', $anuncio->getTitulo(), PDO::PARAM_STR);
        $stmt->bindValue(':descricao', $anuncio->getDescricao(), PDO::PARAM_STR);
        $stmt->bindValue(':localizacao', $anuncio->getLocalizacao(), PDO::PARAM_STR);
        $stmt->bindValue(':data', $anuncio->getData(), PDO::PARAM_STR);
        $stmt->bindValue(':remuneracao', $anuncio->getRemuneracao());
        $stmt->bindValue(':tipo_servico', $anuncio->getTipoServico(), PDO::PARAM_STR);
        $stmt->bindValue(':duracao', $anuncio->getDuracao(), PDO::PARAM_STR);
        $stmt->bindValue(':observacoes', $anuncio->getObservacoes(), PDO::PARAM_STR);
        $stmt->bindValue(':prazo_candidatura', $anuncio->getPrazoCandidatura(), PDO::PARAM_STR);
        $stmt->bindValue(':status', $anuncio->getStatus(), PDO::PARAM_STR);
        
        $stmt->execute();
        return (int)$this->conn->lastInsertId();
    }

    public function atualizar(Anuncio $anuncio): bool
    {
        $sql = "UPDATE anuncio 
                SET titulo = :titulo, descricao = :descricao, localizacao = :localizacao, 
                    data = :data, remuneracao = :remuneracao, tipo_servico = :tipo_servico, 
                    duracao = :duracao, observacoes = :observacoes, prazo_candidatura = :prazo_candidatura, 
                    status = :status 
                WHERE id_anuncio = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $anuncio->getIdAnuncio(), PDO::PARAM_INT);
        $stmt->bindValue(':titulo', $anuncio->getTitulo(), PDO::PARAM_STR);
        $stmt->bindValue(':descricao', $anuncio->getDescricao(), PDO::PARAM_STR);
        $stmt->bindValue(':localizacao', $anuncio->getLocalizacao(), PDO::PARAM_STR);
        $stmt->bindValue(':data', $anuncio->getData(), PDO::PARAM_STR);
        $stmt->bindValue(':remuneracao', $anuncio->getRemuneracao());
        $stmt->bindValue(':tipo_servico', $anuncio->getTipoServico(), PDO::PARAM_STR);
        $stmt->bindValue(':duracao', $anuncio->getDuracao(), PDO::PARAM_STR);
        $stmt->bindValue(':observacoes', $anuncio->getObservacoes(), PDO::PARAM_STR);
        $stmt->bindValue(':prazo_candidatura', $anuncio->getPrazoCandidatura(), PDO::PARAM_STR);
        $stmt->bindValue(':status', $anuncio->getStatus(), PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    public function deletar(int $idAnuncio): bool
    {
        $sql = "DELETE FROM anuncio WHERE id_anuncio = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $idAnuncio, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function mudarStatus(int $idAnuncio, string $status): bool
    {
        $sql = "UPDATE anuncio SET status = :status WHERE id_anuncio = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $idAnuncio, PDO::PARAM_INT);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        return $stmt->execute();
    }
}
