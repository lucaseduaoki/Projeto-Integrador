<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Projeto Integrador • Novo Filme</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">

    <div class="d-flex justify-content-between mb-4">
        <h2>Novo Filme</h2>
        <a href="<?= URL_BASE ?>/filmes" class="btn btn-secondary">Voltar</a>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="<?= URL_BASE ?>/filmes/salvar" method="POST">

                <div class="mb-3">
                    <label>Título</label>
                    <input type="text" name="titulo" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Diretor</label>
                    <input type="text" name="diretor" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Ano</label>
                    <input type="number" name="ano" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Gênero</label>
                    <input type="text" name="genero" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Imagem (caminho)</label>
                    <input type="text" name="imagem" class="form-control">
                </div>

                <button class="btn btn-primary">Salvar</button>

            </form>

        </div>
    </div>

</div>

</body>
</html>