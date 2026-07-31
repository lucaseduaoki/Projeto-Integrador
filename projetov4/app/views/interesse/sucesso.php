<?php
$tituloPagina = 'Candidatura Realizada';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$mensagem = $mensagem ?? 'Candidatura realizada com sucesso!';
?>

<main class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-2xl">
        <div class="bg-white rounded-2xl shadow-sm border border-green-100 overflow-hidden">
            <div class="bg-green-50 px-6 py-5 border-b border-green-100">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-full bg-green-600 text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-green-900">Candidatura enviada</h1>
                        <p class="text-green-800 mt-1">Sua ação foi concluída com sucesso.</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6 text-center">
                <div class="mx-auto w-20 h-20 rounded-full bg-green-100 text-green-700 flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <div>
                    <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-gray-600 mt-2">Agora o contratante poderá visualizar sua candidatura no anúncio.</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <a href="/interesse/historico/visualizar" class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                        Ir para meu histórico
                    </a>
                    <a href="/vagas" class="flex-1 text-center bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                        Explorar mais vagas
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
