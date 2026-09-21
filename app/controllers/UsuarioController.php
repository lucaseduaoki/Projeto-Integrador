<?php

namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
use app\models\Usuario;
use app\services\UsuarioService;

class UsuarioController extends Controller
{
    private UsuarioService $service;

    public function __construct()
    {
        $this->service = new UsuarioService();
    }

    /**
     * Exibir página de cadastro
     */
    public function exibirCadastro(): void
    {
        $this->view('usuario/cadastro', [
        ]);
    }

    /**
     * Processar cadastro (já feito em AutenticacaoController)
     */
    public function cadastrar(): void
    {
        $this->redirect(URL_BASE . '/cadastro');
    }

    /**
     * Exibir perfil do usuário logado
     */
    public function exibirPerfil(): void
    {
        $this->autenticacaoRequired();
        
        $usuario = $this->usuarioLogado();
        $habilidades = $this->service->buscarHabilidades($usuario->getIdUsuario());
        
        $this->view('usuario/perfil', [
            'usuario' => $usuario,
            'habilidades' => $habilidades
        ]);
    }

    /**
     * Editar perfil do usuário
     */
    public function editarPerfil(): void
    {
        error_log("Dados recebidos para editar perfil: " . print_r($_POST, true));

        $this->autenticacaoRequired();

        $usuario = $this->usuarioLogado();

        // Sanitizar entrada
        $nome = htmlspecialchars(trim($_POST['nome'] ?? ''), ENT_QUOTES, 'UTF-8');
        $telefone = htmlspecialchars(trim($_POST['telefone'] ?? ''), ENT_QUOTES, 'UTF-8');
        $descricao = htmlspecialchars(trim($_POST['descricao'] ?? ''), ENT_QUOTES, 'UTF-8');
        $documento = htmlspecialchars(trim($_POST['documento'] ?? ''), ENT_QUOTES, 'UTF-8');
        $nomeResponsavel = trim($_POST['nome_responsavel'] ?? '');
        $novosPapeis = array_values(array_intersect(
            array_filter((array)($_POST['adicionar_papeis'] ?? []), 'is_string'),
            ['TRABALHADOR', 'CONTRATANTE']
        ));
        $localizacao = htmlspecialchars(trim($_POST['localizacao'] ?? ''), ENT_QUOTES, 'UTF-8');
        
        // Pessoa física pode passar a atuar também no outro papel (RN02); empresa não acumula,
        // administrador não recebe papéis por aqui e ninguém remove papel pelo perfil.
        $validador = new Validador();
        if (!empty($novosPapeis) && ($usuario->isPessoaJuridica() || $usuario->isAdmin())) {
            $validador->erro('adicionar_papeis', 'Somente pessoa física pode acumular os papéis de trabalhador e contratante.');
            $novosPapeis = [];
        }

        $trabalhadorFinal = $usuario->isTrabalhador() || in_array('TRABALHADOR', $novosPapeis, true);
        $contratanteFinal = $usuario->isContratante() || in_array('CONTRATANTE', $novosPapeis, true);

        // Validar
        $validador->documentoPorTipoPessoa('documento', $documento, $usuario->getTipoPessoa());
        $validador->responsavelPrestadora('nome_responsavel', $nomeResponsavel, $usuario->getTipoPessoa(), $trabalhadorFinal);
        $documento = preg_replace('/\D/', '', $documento);

        $validador->obrigatorio('nome', $nome)
            ->obrigatorio('documento', $documento)
            ->maximo('nome', $nome, 100)
            ->maximo('telefone', $telefone, 20)
            ->maximo('descricao', $descricao, 500)
            ->maximo('documento', $documento, 20)
            ->maximo('localizacao', $localizacao, 100);

        if ($validador->temErros()) {
            $this->view('usuario/perfil', [
                'usuario' => $usuario,
                'erros' => $validador->getErros(),
            ]);
            return;
        }

        try {
            // Atualizar dados
            $usuario->setNome($nome);
            $usuario->setTelefone($telefone ?: null);
            $usuario->setDescricao($descricao ?: null);
            $usuario->setDocumento($documento);

            // Foto de perfil (opcional): validada e gravada pelo service
            $fotoAnterior = $usuario->getFotoPerfil();
            $novaFoto = null;
            $enviouFoto = isset($_FILES['foto_perfil']) && ($_FILES['foto_perfil']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

            if ($enviouFoto) {
                $novaFoto = $this->service->salvarFotoPerfil($_FILES['foto_perfil']);
                $usuario->setFotoPerfil($novaFoto);
            }
            $usuario->setIsTrabalhador($trabalhadorFinal);
            $usuario->setIsContratante($contratanteFinal);
            $usuario->setNomeResponsavel(($usuario->isPessoaJuridica() && $trabalhadorFinal) ? $nomeResponsavel : null);
            error_log("Print Usuario antes de atualizar: " . print_r($usuario, true));
            $this->service->atualizarPerfil($usuario);

            // A foto antiga só é apagada depois que a nova foi gravada com sucesso
            if ($novaFoto !== null) {
                $this->service->removerArquivoFoto($fotoAnterior);
            }

            // Atualizar sessão
            $_SESSION['usuario_logado'] = $usuario;

            $this->view('usuario/perfil', [
                'usuario' => $usuario,
                'sucesso' => 'Perfil atualizado com sucesso!',
            ]);
        } catch (\Exception $e) {
            // Falha depois de gravar a nova foto: não deixa arquivo órfão nem troca a foto do usuário
            if (isset($novaFoto) && $novaFoto !== null) {
                $this->service->removerArquivoFoto($novaFoto);
                $usuario->setFotoPerfil($fotoAnterior);
            }

            $this->view('usuario/perfil', [
                'usuario' => $usuario,
                'erro' => $e->getMessage(),
            ]);
        }
    }

    public function exibirFormEditarPerfil(): void
    {
        $this->autenticacaoRequired();

        $usuario = $this->usuarioLogado();
        $habilidadesUsuario = array_map(
            fn($h) => $h->getIdHabilidade(),
            $this->service->buscarHabilidades($usuario->getIdUsuario())
        );

        $this->view('usuario/perfil_editar', [
            'usuario' => $usuario,
            'habilidades' => $this->service->listarHabilidades(),
            'habilidadesUsuario' => $habilidadesUsuario
        ]);
    }
}
