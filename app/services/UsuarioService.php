<?php

namespace app\services;

use app\database\ConnectionFactory;
use app\helpers\UploadImagemValidador;
use app\models\Usuario;
use app\repositories\UsuarioRepository;
use Exception;

class UsuarioService
{
    private UsuarioRepository $repository;

    public function __construct()
    {
        $this->repository = new UsuarioRepository();
    }

    /**
     * Registrar novo usuário
     */
    public function registrar(
        string $nome,
        string $email,
        string $senha,
        bool $isTrabalhador,
        bool $isContratante,
        ?string $telefone = null,
        ?string $documento = null,
        string $tipoPessoa = 'PF',
        ?string $nomeResponsavel = null
    ): Usuario {
        // Verificar se email já existe
        if ($this->repository->emailExiste($email)) {
            throw new Exception('Este e-mail já está cadastrado.');
        }

        // Hash da senha
        $senhaHash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);

        // Criar usuário
        $usuario = new Usuario(
            0,
            $nome,
            $email,
            $senhaHash,
            false,
            $isTrabalhador,
            $isContratante,
            $telefone,
            null,
            null,
            $documento,
            1,
            '',
            $tipoPessoa,
            $nomeResponsavel
        );

        $idCriado = $this->repository->criar($usuario);
        $usuario->setIdUsuario($idCriado); // Atribuir ID ao objeto

        return $this->repository->buscarPorId($idCriado);
    }

    /**
     * Buscar usuário por ID
     */
    public function buscarPorId(int $id): ?Usuario
    {
        return $this->repository->buscarPorId($id);
    }

    /**
     * Buscar vários usuários de uma vez, indexado por id_usuario.
     */
    public function buscarPorIds(array $ids): array
    {
        return $this->repository->buscarPorIds($ids);
    }

    /**
     * Buscar usuário por email
     */
    public function buscarPorEmail(string $email): ?Usuario
    {
        return $this->repository->buscarPorEmail($email);
    }

    /**
     * Listar usuários
     */
    public function listar(): array
    {
        return $this->repository->listar();
    }

    /**
     * Listar usuários por tipo
     */
    public function listarPorTipo(string $tipo): array
    {
        return $this->repository->listarPorTipo($tipo);
    }

    /**
     * Atualizar perfil do usuário
     */
    public function atualizarPerfil(Usuario $usuario): bool
    {
        // Verificar se email foi alterado e já existe
        if ($usuario->getEmail() !== $this->repository->buscarPorId($usuario->getIdUsuario())?->getEmail()) {
            if ($this->repository->emailExiste($usuario->getEmail(), $usuario->getIdUsuario())) {
                throw new Exception('Este e-mail já está cadastrado por outro usuário.');
            }
        }

        return $this->repository->atualizar($usuario);
    }

    /**
     * Atualizar senha
     */
    public function atualizarSenha(int $idUsuario, string $senhaAtual, string $novaSenha): bool
    {
        $usuario = $this->repository->buscarPorId($idUsuario);

        if (!$usuario) {
            throw new Exception('Usuário não encontrado.');
        }

        // Verificar senha atual
        if (!password_verify($senhaAtual, $usuario->getSenha())) {
            throw new Exception('Senha atual incorreta.');
        }

        $senhaHash = password_hash($novaSenha, PASSWORD_BCRYPT, ['cost' => 12]);
        return $this->repository->atualizarSenha($idUsuario, $senhaHash);
    }

    private const FOTO_PASTA = 'uploads/perfis/';

    /**
     * Valida e grava a foto enviada em $_FILES['foto_perfil'] e devolve o caminho relativo
     * (ex.: uploads/perfis/ab12...jpg). O nome do arquivo é gerado aqui: o nome enviado pelo
     * usuário nunca é usado, e a extensão vem do tipo detectado no conteúdo.
     */
    public function salvarFotoPerfil(array $arquivo): string
    {
        $validado = UploadImagemValidador::validar($arquivo);

        $pasta = dirname(__DIR__, 2) . '/public/' . self::FOTO_PASTA;

        if (!is_dir($pasta) && !mkdir($pasta, 0755, true)) {
            throw new Exception('Não foi possível salvar a foto. Tente novamente mais tarde.');
        }

        $nome = bin2hex(random_bytes(16)) . '.' . $validado['extensao'];

        if (!move_uploaded_file($validado['tmp'], $pasta . $nome)) {
            throw new Exception('Não foi possível salvar a foto. Tente novamente mais tarde.');
        }

        chmod($pasta . $nome, 0644);

        return self::FOTO_PASTA . $nome;
    }

    /**
     * Apaga um arquivo de foto gerado pelo sistema. Ignora qualquer caminho fora da pasta de fotos.
     */
    public function removerArquivoFoto(?string $caminho): void
    {
        if ($caminho === null || !str_starts_with($caminho, self::FOTO_PASTA)) {
            return;
        }

        $arquivo = dirname(__DIR__, 2) . '/public/' . self::FOTO_PASTA . basename($caminho);

        if (is_file($arquivo)) {
            @unlink($arquivo);
        }
    }

    /**
     * Atualizar foto de perfil
     */
    public function atualizarFotoPerfil(int $idUsuario, string $caminhoFoto): bool
    {
        // Validar que arquivo existe
        if (!file_exists($caminhoFoto)) {
            throw new Exception('Arquivo de imagem não encontrado.');
        }

        return $this->repository->atualizarFotoPerfil($idUsuario, $caminhoFoto);
    }

    /**
     * Bloquear usuário
     */
    public function bloquear(int $idUsuario): bool
    {
        $usuario = $this->repository->buscarPorId($idUsuario);
        if (!$usuario) {
            throw new Exception('Usuário não encontrado.');
        }
        return $this->repository->bloquear($idUsuario);
    }

    /**
     * Desbloquear usuário
     */
    public function desbloquear(int $idUsuario): bool
    {
        $usuario = $this->repository->buscarPorId($idUsuario);
        if (!$usuario) {
            throw new Exception('Usuário não encontrado.');
        }
        return $this->repository->desbloquear($idUsuario);
    }

    /**
     * Adicionar habilidade a trabalhador
     */
    public function adicionarHabilidade(int $idUsuario, int $idHabilidade): bool
    {
        return $this->repository->adicionarHabilidade($idUsuario, $idHabilidade);
    }

    /**
     * Remover habilidade de trabalhador
     */
    public function removerHabilidade(int $idUsuario, int $idHabilidade): bool
    {
        return $this->repository->removerHabilidade($idUsuario, $idHabilidade);
    }

    /**
     * Buscar habilidades de usuário
     */
    public function buscarHabilidades(int $idUsuario): array
    {
        return $this->repository->buscarHabilidades($idUsuario);
    }

    /**
     * Regrava o conjunto de habilidades do prestador. Só vale para quem atua como trabalhador
     * (RN: habilidades só para prestadores) e só aceita ids de habilidades que existem.
     * A troca é atômica: ou grava todas ou mantém as anteriores.
     */
    public function definirHabilidades(Usuario $usuario, array $ids): void
    {
        if (!$usuario->isTrabalhador()) {
            throw new Exception('Somente prestadores de serviço têm habilidades.');
        }

        // Só inteiros positivos em forma de dígitos: "1;DROP" ou "abc" são recusados, não convertidos
        foreach ($ids as $id) {
            if (!ctype_digit((string)$id) || (int)$id <= 0) {
                throw new Exception('Habilidade inválida.');
            }
        }

        $ids = array_values(array_unique(array_map('intval', $ids)));

        $existentes = array_map(fn($h) => $h->getIdHabilidade(), $this->repository->listarHabilidades());

        if (array_diff($ids, $existentes)) {
            throw new Exception('Habilidade inválida.');
        }

        $pdo = ConnectionFactory::getConnection();
        $pdo->beginTransaction();

        try {
            $this->repository->limparHabilidades($usuario->getIdUsuario());

            foreach ($ids as $id) {
                $this->repository->adicionarHabilidade($usuario->getIdUsuario(), $id);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function listarHabilidades(): array
    {
        return $this->repository->listarHabilidades();
    }

    /**
     * Limpar habilidades
     */
    public function limparHabilidades(int $idUsuario): bool
    {
        return $this->repository->limparHabilidades($idUsuario);
    }
}

