<?php 

namespace app\services;

use app\models\Filme;
use app\repositories\FilmeRepository;

class FilmeService {

    private FilmeRepository $repository;

    public function __construct(){
        $this->repository = new FilmeRepository();
    }

    public function getFilmes(){
        // lugar para validações se necessário
        return $this->repository->getFilmes();
    }

    public function getFilme(int $id){
        return $this->repository->getFilme($id);
    }

    public function saveFilme(Filme $filme){
        return $this->repository->saveFilme($filme);
    }

    public function updateFilme(Filme $filme){
        return $this->repository->updateFilme($filme);
    }

    public function deleteFilme(int $id){
        return $this->repository->deleteFilme($id);
    }

}