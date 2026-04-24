<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Detalhes do Filme</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container py-5">

<?php if (!empty($filme)): ?>

    <div class="d-flex justify-content-between mb-4">
        <h2><?= $filme['titulo'] ?></h2>
        <a href="<?= URL_BASE ?>/filmes" class="btn btn-secondary">Voltar</a>
    </div>

    <div class="card">
        <div class="card-body">

            <?php if (!empty($filme['imagem'])): ?>
               <img src="<?= $filme['imagem'] ?>" class="img-fluid mb-3">
            <?php endif; ?>

            <p><strong>Diretor:</strong> <?= $filme['diretor'] ?></p>
            <p><strong>Ano:</strong> <?= $filme['ano'] ?></p>
            <p><strong>Gênero:</strong> <?= $filme['genero'] ?></p>

        </div>
    </div>

<?php else: ?>

    <div class="alert alert-warning">
        Filme não encontrado.
    </div>

<?php endif; ?>

</div>

</body>
</html>