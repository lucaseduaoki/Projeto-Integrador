<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Vaga;
use PDO;

class VagaRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    /**
     * Converte um registro do banco em objeto Vaga.
     */
    private function mapear(array $row): Vaga
    {
return new Vaga(
    (int)$row['id_vaga'],
    (int)$row['id_contratante'],
    (int)$row['id_categoria'],
    $row['titulo'],
    $row['descricao'],
    $row['localizacao'],
    $row['remuneracao'] !== null ? (float)$row['remuneracao'] : null,
    $row['data_publicacao'],
    $row['data_limite'],
    $row['trabalhadores_limite'],
    $row['status'],
    isset($row['total_aceitos']) ? (int)$row['total_aceitos'] : 0,
    (bool)($row['is_user_active'] ?? true),
    isset($row['horario']) ? substr($row['horario'], 0, 5) : null,
    $row['tipo_servico'] ?? 'FIXO',
    $row['duracao'] ?? null,
    $row['observacoes'] ?? null
);
    }

    public function buscarPorId(int $id): ?Vaga
    {
        $sql = "
            SELECT
                v.*,
                COUNT(i.id_interesse) AS total_aceitos
            FROM vaga v
            LEFT JOIN interesse i
                ON i.id_vaga = v.id_vaga
            AND i.status = 'ACEITO'
            WHERE v.id_vaga = :id
            GROUP BY v.id_vaga
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapear($row) : null;
    }

    public function listar(int $limit = 50, int $offset = 0): array
    {
        $sql = "
            SELECT *
            FROM vaga
            WHERE status = 'ATIVA'
              AND is_user_active = 1
            ORDER BY data_publicacao DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resultado[] = $this->mapear($row);
        }

        return $resultado;
    }

    public function listarPorContratante(int $idContratante): array
    {
$sql = "
    SELECT
        v.*,
        COUNT(i.id_interesse) AS total_aceitos
    FROM vaga v
    LEFT JOIN interesse i
        ON i.id_vaga = v.id_vaga
       AND i.status = 'ACEITO'
    WHERE v.id_contratante = :id
    GROUP BY v.id_vaga
    ORDER BY v.data_publicacao DESC
";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $idContratante, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resultado[] = $this->mapear($row);
        }

        return $resultado;
    }

    /**
     * Busca vagas ativas. Filtros aceitos (todos opcionais): titulo, localizacao, tipo_servico.
     */
    public function buscar(array $filtros = []): array
    {
        $sql = "
            SELECT *
            FROM vaga
            WHERE status = 'ATIVA'
              AND is_user_active = 1
        ";

        $params = [];

        if (($filtros['titulo'] ?? '') !== '') {
            $sql .= " AND (titulo LIKE :titulo OR descricao LIKE :titulo)";
            $params['titulo'] = "%{$filtros['titulo']}%";
        }

        if (($filtros['localizacao'] ?? '') !== '') {
            $sql .= " AND localizacao LIKE :localizacao";
            $params['localizacao'] = "%{$filtros['localizacao']}%";
        }

        if (($filtros['tipo_servico'] ?? '') !== '') {
            $sql .= " AND tipo_servico = :tipo_servico";
            $params['tipo_servico'] = $filtros['tipo_servico'];
        }

        $sql .= " ORDER BY data_publicacao DESC";

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $nome => $valor) {
            $stmt->bindValue(":$nome", $valor);
        }

        $stmt->execute();

        $resultado = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resultado[] = $this->mapear($row);
        }

        return $resultado;
    }

    public function criar(Vaga $vaga): int
    {
        $sql = "
            INSERT INTO vaga
            (
                id_contratante,
                id_categoria,
                titulo,
                descricao,
                localizacao,
                remuneracao,
                data_limite,
                horario,
                tipo_servico,
                duracao,
                observacoes,
                trabalhadores_limite,
                status
            )
            VALUES
            (
                :id_contratante,
                :id_categoria,
                :titulo,
                :descricao,
                :localizacao,
                :remuneracao,
                :data_limite,
                :horario,
                :tipo_servico,
                :duracao,
                :observacoes,
                :trabalhadores_limite,
                'ATIVA'
            )
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':id_contratante', $vaga->getIdContratante(), PDO::PARAM_INT);
        $stmt->bindValue(':id_categoria', $vaga->getIdCategoria(), PDO::PARAM_INT);
        $stmt->bindValue(':titulo', $vaga->getTitulo());
        $stmt->bindValue(':descricao', $vaga->getDescricao());
        $stmt->bindValue(':localizacao', $vaga->getLocalizacao());
        $stmt->bindValue(':remuneracao', $vaga->getRemuneracao());
        $stmt->bindValue(':data_limite', $vaga->getDataLimite());
        $stmt->bindValue(':horario', $vaga->getHorario());
        $stmt->bindValue(':tipo_servico', $vaga->getTipoServico());
        $stmt->bindValue(':duracao', $vaga->getDuracao());
        $stmt->bindValue(':observacoes', $vaga->getObservacoes());
        $stmt->bindValue(':trabalhadores_limite', $vaga->getTrabalhadoresLimite(), PDO::PARAM_INT);

        $stmt->execute();

        return (int)$this->conn->lastInsertId();
    }

    public function atualizar(Vaga $vaga): bool
    {
        $sql = "
            UPDATE vaga
            SET
                id_categoria = :id_categoria,
                titulo = :titulo,
                descricao = :descricao,
                localizacao = :localizacao,
                remuneracao = :remuneracao,
                data_limite = :data_limite,
                horario = :horario,
                tipo_servico = :tipo_servico,
                duracao = :duracao,
                observacoes = :observacoes,
                trabalhadores_limite = :trabalhadores_limite
            WHERE id_vaga = :id
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':id', $vaga->getIdVaga(), PDO::PARAM_INT);
        $stmt->bindValue(':id_categoria', $vaga->getIdCategoria(), PDO::PARAM_INT);
        $stmt->bindValue(':titulo', $vaga->getTitulo());
        $stmt->bindValue(':descricao', $vaga->getDescricao());
        $stmt->bindValue(':localizacao', $vaga->getLocalizacao());
        $stmt->bindValue(':remuneracao', $vaga->getRemuneracao());
        $stmt->bindValue(':data_limite', $vaga->getDataLimite());
        $stmt->bindValue(':horario', $vaga->getHorario());
        $stmt->bindValue(':tipo_servico', $vaga->getTipoServico());
        $stmt->bindValue(':duracao', $vaga->getDuracao());
        $stmt->bindValue(':observacoes', $vaga->getObservacoes());
        $stmt->bindValue(':trabalhadores_limite', $vaga->getTrabalhadoresLimite(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function deletar(int $idVaga): bool
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM vaga WHERE id_vaga = :id"
        );

        $stmt->bindValue(':id', $idVaga, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function mudarStatus(int $idVaga, string $status): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE vaga SET status = :status WHERE id_vaga = :id"
        );

        $stmt->bindValue(':id', $idVaga, PDO::PARAM_INT);
        $stmt->bindValue(':status', $status);

        return $stmt->execute();
    }


}