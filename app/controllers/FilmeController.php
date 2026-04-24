<?php

namespace app\controllers;

use app\core\Controller;
use app\models\Filme;
use app\services\FilmeService;

class FilmeController extends Controller
{
    private FilmeService $service;

    public function __construct()
    {
        $this->service = new FilmeService();
    }

    // listar todos os filmes
    public function listarTodos()
    {
        $data['lista'] = $this->service->getFilmes();
        $this->view('filmes/filmes_list', $data);
    }

    // ver um filme específico
    public function verFilme()
    {
        if (!isset($_GET['id'])) {
            $this->redirect(URL_BASE . '/filmes');
        }

        $id = $_GET['id'];

        $data['filme'] = $this->service->getFilme($id);

        $this->view('filmes/filmes_show', $data);
    }

    // tela de criação
    public function criar()
    {
        $this->autenticacaoRequired();

        $this->view('filmes/filmes_create', []);
    }

    // salvar filme
    public function salvar()
    {
        $this->adminRequired();

        $titulo = $_POST['titulo'];
        $diretor = $_POST['diretor'];
        $ano = $_POST['ano'];
        $genero = $_POST['genero'];
        $imagem = $_POST['imagem'] ?? '';

        $filme = new Filme();

        $filme->setTitulo($titulo);
        $filme->setDiretor($diretor);
        $filme->setAno($ano);
        $filme->setGenero($genero);
        $filme->setImagem($imagem);

        $this->service->saveFilme($filme);

        $this->redirect(URL_BASE . '/filmes');
    }

    // tela de edição
    public function editar()
    {
        $this->autenticacaoRequired();

        if (!isset($_GET['id'])) {
            $this->redirect(URL_BASE . '/filmes');
        }

        $id = $_GET['id'];

        $data['filme'] = $this->service->getFilme($id);

        $this->view('filmes/filmes_edit', $data);
    }

    // atualizar filme
    public function atualizar()
    {
        $this->adminRequired();

        $id = $_POST['id'];
        $titulo = $_POST['titulo'];
        $diretor = $_POST['diretor'];
        $ano = $_POST['ano'];
        $genero = $_POST['genero'];
        $imagem = $_POST['imagem'] ?? '';

        $filme = new Filme();

        $filme->setId($id);
        $filme->setTitulo($titulo);
        $filme->setDiretor($diretor);
        $filme->setAno($ano);
        $filme->setGenero($genero);
        $filme->setImagem($imagem);

        $this->service->updateFilme($filme);

        $this->redirect(URL_BASE . '/filmes');
    }

    // excluir filme
    public function excluir()
    {
        $this->adminRequired();

        if (!isset($_GET['id'])) {
            $this->redirect(URL_BASE . '/filmes');
        }

        $id = $_GET['id'];

        $this->service->deleteFilme($id);

        $this->redirect(URL_BASE . '/filmes');
    }
}