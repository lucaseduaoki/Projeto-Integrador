<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Projeto Integrador • Lista de Filmes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

    <div class="container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Lista de Filmes</h2>
            <a href="<?= URL_BASE ?>/filmes/criar" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Novo Filme
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">Imagem</th>
                                <th class="px-4 py-3">Título</th>
                                <th class="px-4 py-3">Diretor</th>
                                <th class="px-4 py-3">Ano</th>
                                <th class="px-4 py-3 text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lista as $filme): ?>
                                <tr>

                                    <td class="px-4 py-3 align-middle">
                                        <img src="<?= $filme['imagem'] ?>"
                                            alt="Imagem do filme"
                                            style="width: 80px; height: auto; border-radius: 6px;">
                                    </td>

                                    <td class="px-4 py-3 align-middle"><?= $filme['titulo'] ?></td>
                                    <td class="px-4 py-3 align-middle"><?= $filme['diretor'] ?></td>
                                    <td class="px-4 py-3 align-middle"><?= $filme['ano'] ?></td>
                                    <td class="px-4 py-3 text-end">
                                        <a href="<?= URL_BASE ?>/filmes/ver?id=<?= $filme['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                        <a href="<?= URL_BASE ?>/filmes/editar?id=<?= $filme['id'] ?>" class="btn btn-sm btn-outline-warning">
                                            Editar
                                        </a>
                                        <a href="<?= URL_BASE ?>/filmes/excluir?id=<?= $filme['id'] ?>" class="btn btn-sm btn-outline-danger">
                                            Excluir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</body>

</html>