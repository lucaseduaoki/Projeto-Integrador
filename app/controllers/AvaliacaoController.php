<?php

namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
use app\models\Avaliacao;
use app\services\AvaliacaoService;

class AvaliacaoController extends Controller
{
    private AvaliacaoService $service;

    public function __construct()
    {
        $this->service = new AvaliacaoService();
    }

    /**
     * Exibir formulário de avaliação
     */
    public function exibirFormAvaliar(): void
    {
        $this->autenticacaoRequired();

        $idAnuncio = (int)($_GET['id_anuncio'] ?? 0);
        $idAvaliado = (int)($_GET['id_avaliado'] ?? 0);

        if ($idAnuncio <= 0 || $idAvaliado <= 0) {
            $this->redirect(URL_BASE . '/anuncios');
        }

        $this->view('avaliacao/form', [
            'idAnuncio' => $idAnuncio,
            'idAvaliado' => $idAvaliado,
        ]);
    }

    /**
     * Criar avaliação
     */
    public function avaliar(): void
    {
        $this->autenticacaoRequired();

        $usuario = $this->usuarioLogado();
        $idAnuncio = (int)($_POST['id_anuncio'] ?? 0);
        $idAvaliado = (int)($_POST['id_avaliado'] ?? 0);
        $nota = (int)($_POST['nota'] ?? 0);
        $comentario = htmlspecialchars(trim($_POST['comentario'] ?? ''), ENT_QUOTES, 'UTF-8');

        // Validar
        $validador = new Validador();
        $validador->obrigatorio('nota', $nota)
                  ->notaAvaliacao('nota', $nota);

        if ($validador->temErros()) {
            $this->view('avaliacao/form', [
                'idAnuncio' => $idAnuncio,
                'idAvaliado' => $idAvaliado,
                'erros' => $validador->getErros(),
            ]);
            return;
        }

        try {
            $this->service->criar(
                $usuario->getIdUsuario(),
                $idAvaliado,
                $idAnuncio,
                $nota,
                $comentario ?: null
            );

            $this->view('avaliacao/sucesso', [
                'mensagem' => 'Avaliação registrada com sucesso!'
            ]);
        } catch (\Exception $e) {
            $this->view('avaliacao/form', [
                'idAnuncio' => $idAnuncio,
                'idAvaliado' => $idAvaliado,
                'erro' => $e->getMessage(),
            ]);
        }
    }
}
