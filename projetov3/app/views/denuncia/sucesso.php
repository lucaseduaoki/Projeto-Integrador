<?php
$tituloPagina = 'Denúncia Registrada';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$mensagem = $mensagem ?? 'Denúncia registrada com sucesso.';
?>

<main class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-xl">
        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-8 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-3xl font-bold">
                ✓
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-3">Denúncia enviada</h1>
            <p class="text-gray-600 mb-6"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p>

            <div class="flex gap-3 justify-center">
                <a href="/anuncios" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                    Voltar aos anúncios
                </a>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>