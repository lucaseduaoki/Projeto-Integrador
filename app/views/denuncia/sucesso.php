<?php
$tituloPagina = 'Denúncia Registrada';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$mensagem = $mensagem ?? 'Denúncia registrada com sucesso.';
?>

<main class="flex-1 flex items-center justify-start px-4 py-12">
    <div class="w-full max-w-xl">
        <div class="bg-white rounded-md border border-gray-200 p-8 text-left">

            <h1 class="text-2xl font-bold text-gray-900 mb-3">Denúncia enviada</h1>
            <p class="text-gray-600 mb-6"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p>

            <div class="flex gap-3 justify-start">
                <a href="<?= URL_BASE ?>/vagas" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition-colors duration-200">
                    Voltar aos anúncios
                </a>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>