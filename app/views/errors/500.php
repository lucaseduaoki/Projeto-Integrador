<?php
$tituloPagina = 'Erro no Sistema';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';
?>

<main class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="text-center">
        <div class="mb-6">
            <h1 class="font-display text-9xl font-extrabold text-blue-600 mb-2">500</h1>
            <h2 class="text-3xl font-bold text-gray-900">Algo deu errado</h2>
        </div>

        <p class="text-gray-600 text-lg mb-8 max-w-md mx-auto">
            Não conseguimos concluir a sua solicitação agora. Tente novamente em instantes.
        </p>

        <a href="<?= URL_BASE ?>/vagas" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded transition-colors duration-200">
            Voltar às vagas
        </a>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
