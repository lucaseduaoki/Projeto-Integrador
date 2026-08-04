<?php

namespace app\services;

use app\models\Humorista;
use app\repositories\HumoristaRepository;

class HumoristaService
{
    private HumoristaRepository $repository;
    private ?string $erro = null;

    public function __construct()
    {
        $this->repository = new HumoristaRepository();
    }

    public function getErro(): ?string
    {
        return $this->erro;
    }

    public function getHumoristas(): array
    {
        return $this->repository->getHumoristas();
    }

    public function getHumoristaById(int $id)
    {
        return $this->repository->getHumoristaById($id);
    }

    public function saveHumorista(Humorista $humorista): bool
    {
        $this->erro = null;

        $existente = $this->repository->getHumoristaByNomeArtistico($humorista->getNomeArtistico());
        if ($existente) {
            $this->erro = 'Ja existe um humorista com esse nome artistico.';
            return false;
        }

        return $this->repository->saveHumorista($humorista);
    }

    public function updateHumorista(Humorista $humorista): bool
    {
        $this->erro = null;

        $existente = $this->repository->getHumoristaByNomeArtistico($humorista->getNomeArtistico());
        if ($existente && (int)$existente['id'] !== $humorista->getId()) {
            $this->erro = 'Ja existe um humorista com esse nome artistico.';
            return false;
        }

        return $this->repository->updateHumorista($humorista);
    }

    public function deleteHumorista(int $id): bool
    {
        $this->erro = null;

        $humorista = $this->repository->getHumoristaById($id);
        if (!$humorista) {
            $this->erro = 'Humorista nao encontrado.';
            return false;
        }

        if ((int)$humorista['em_atividade'] === 1) {
            $this->erro = 'Nao e permitido excluir humoristas em atividade.';
            return false;
        }

        return $this->repository->deleteHumorista($id);
    }
}
