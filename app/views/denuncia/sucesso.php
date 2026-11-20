<?php
$tituloPagina = 'Denúncia Registrada';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$mensagem = $mensagem ?? 'Denúncia registrada com sucesso.';
?>

<main class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg">
        <div class="bg-white rounded-md border border-gray-200 p-8 text-center">

            <div class="w-16 h-16 rounded-full bg-green-100 text-green-600 flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Denúncia enviada</h1>
            <p class="text-gray-600 mb-8"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p>

            <a href="<?= URL_BASE ?>/vagas" class="inline-block w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-md transition-colors">
                Voltar aos anúncios
            </a>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>