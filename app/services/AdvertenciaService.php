<?php

namespace app\services;

use app\repositories\AdvertenciaRepository;

class AdvertenciaService
{
    private AdvertenciaRepository $repository;

    public function __construct()
    {
        $this->repository = new AdvertenciaRepository();
    }

    public function listarNaoVisualizadas(int $idUsuario): array
    {
        return $this->repository->listarNaoVisualizadas($idUsuario);
    }

    public function dispensar(int $idAdvertencia, int $idUsuario): bool
    {
        return $this->repository->marcarVisualizada($idAdvertencia, $idUsuario);
    }
}
