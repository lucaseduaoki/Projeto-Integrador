<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Filme</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">

    <h2>Editar Filme</h2>

    <form action="<?= URL_BASE ?>/filmes/atualizar" method="POST">

        <input type="hidden" name="id" value="<?= $filme['id'] ?>">

        <input type="text" name="titulo" value="<?= $filme['titulo'] ?>" class="form-control mb-2">
        <input type="text" name="diretor" value="<?= $filme['diretor'] ?>" class="form-control mb-2">
        <input type="number" name="ano" value="<?= $filme['ano'] ?>" class="form-control mb-2">
        <input type="text" name="genero" value="<?= $filme['genero'] ?>" class="form-control mb-2">
        <input type="text" name="imagem" value="<?= $filme['imagem'] ?>" class="form-control mb-2">

        <button class="btn btn-success">Atualizar</button>

    </form>

</div>

</body>
</html>