<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login • Projeto Integrador</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            border-radius: 1rem;
            border: none;
        }

        .login-card .card-title {
            font-weight: 700;
            color: #333;
        }

        .btn-login {
            background: linear-gradient(to right, #667eea, #764ba2);
            border: none;
            font-weight: 600;
            padding: 0.8rem;
        }

        .btn-login:hover {
            opacity: 0.9;
        }

        .input-group-text {
            background-color: transparent;
            border-right: none;
        }

        .form-control {
            border-left: none;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #dee2e6;
        }
    </style>
</head>

<body>

    <div class="card login-card shadow-lg">
        <div class="card-body">
            <div class="text-center mb-4">
                <i class="bi bi-shield-lock-fill text-primary" style="font-size: 3rem;"></i>
                <h3 class="card-title mt-2">Acesso ao Sistema</h3>
                <p class="text-muted small">Entre com suas credenciais para continuar</p>
            </div>

            <?php if (isset($erros)): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                        <?= is_array($erros) ? implode('<br>', $erros) : $erros ?>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?= URL_BASE ?>/logar" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label small fw-bold">E-mail</label>
                    <div class="input-group">
                        <span class="input-group-text text-muted">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="nome@exemplo.com" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="senha" class="form-label small fw-bold">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text text-muted">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="senha" name="senha" placeholder="Sua senha" required>
                    </div>
                </div>

                <div class="d-grid shadow-sm">
                    <button type="submit" class="btn btn-primary btn-login text-white">
                        Entrar <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>