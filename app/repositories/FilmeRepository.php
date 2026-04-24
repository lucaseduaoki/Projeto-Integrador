<?php 

namespace app\repositories;

use app\models\Filme;
use app\database\ConnectionFactory;
use PDO;

class FilmeRepository {

    private $conn;

    public function __construct(){
        $this->conn = ConnectionFactory::getConnection();
    }

    // LISTAR TODOS
    public function getFilmes(){
        $sql = "SELECT * FROM filmes";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // BUSCAR UM
    public function getFilme(int $id){
        $sql = "SELECT * FROM filmes WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // SALVAR
    public function saveFilme(Filme $filme){
        $sql = "INSERT INTO filmes (titulo, diretor, ano, genero, imagem)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $filme->getTitulo(),
            $filme->getDiretor(),
            $filme->getAno(),
            $filme->getGenero(),
            $filme->getImagem()
        ]);
    }

    // ATUALIZAR
    public function updateFilme(Filme $filme){
        $sql = "UPDATE filmes 
                SET titulo = ?, diretor = ?, ano = ?, genero = ?, imagem = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $filme->getTitulo(),
            $filme->getDiretor(),
            $filme->getAno(),
            $filme->getGenero(),
            $filme->getImagem(),
            $filme->getId()
        ]);
    }

    // DELETAR
    public function deleteFilme(int $id){
        $sql = "DELETE FROM filmes WHERE id = ?";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([$id]);
    }

}