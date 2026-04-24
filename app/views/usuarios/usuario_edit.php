<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Projeto Integrador • <?= isset($usuario['id']) ? 'Editar' : 'Novo' ?> Usuário</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><?= isset($usuario['id']) ? 'Editar' : 'Novo' ?> Usuário</h2>
            <a href="<?= URL_BASE ?>/usuarios" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card shadow-sm col-md-8 mx-auto">
            <div class="card-body p-4">
                <form action="<?= URL_BASE ?>/usuarios/<?= isset($usuario['id']) ? 'atualizar' : 'salvar' ?>" method="post">
                    <?php if (isset($usuario['id'])): ?>
                        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nomeUsuario" class="form-label">Nome de Usuário</label>
                        <input type="text" class="form-control" id="nomeUsuario" name="nomeUsuario" value="<?= $usuario['nomeUsuario'] ?? '' ?>">
                        <?php if (isset($erros['nomeUsuario'])): ?>
                            <div class="text-danger small"><?= $erros['nomeUsuario'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= $usuario['email'] ?? '' ?>">
                        <?php if (isset($erros['email'])): ?>
                            <div class="text-danger small"><?= $erros['email'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha <?= isset($usuario['id']) ? '(Preencha apenas se quiser alterar)' : '' ?></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="senha" name="senha">
                            <button class="btn btn-outline-secondary" type="button" id="toggleSenha">
                                <i class="bi bi-eye" id="iconSenha"></i>
                            </button>
                        </div>
                        <?php if (isset($erros['senha'])): ?>
                            <div class="text-danger small"><?= $erros['senha'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="perfil" class="form-label">Perfil</label>
                        <select class="form-select" id="perfil" name="perfil">
                            <option value="user" <?= (isset($usuario['perfil']) && $usuario['perfil'] == 'user') ? 'selected' : '' ?>>Usuário Padrão</option>
                            <option value="admin" <?= (isset($usuario['perfil']) && $usuario['perfil'] == 'admin') ? 'selected' : '' ?>>Administrador</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle"></i> <?= isset($usuario['id']) ? 'Atualizar' : 'Salvar' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleSenha = document.querySelector('#toggleSenha');
        const senhaInput = document.querySelector('#senha');
        const iconSenha = document.querySelector('#iconSenha');

        if (toggleSenha) {
            toggleSenha.addEventListener('click', function() {
                const type = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
                senhaInput.setAttribute('type', type);
                
                iconSenha.classList.toggle('bi-eye');
                iconSenha.classList.toggle('bi-eye-slash');
            });
        }
    </script>
</body>

</html>
