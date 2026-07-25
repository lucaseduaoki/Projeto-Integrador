<?php
$tituloPagina = 'Acesso Negado';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';
?>

<main class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="text-center">
        <div class="mb-6">
            <h1 class="text-9xl font-black bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">403</h1>
            <h2 class="text-3xl font-bold text-gray-900">Acesso Negado</h2>
        </div>
        
        <p class="text-gray-600 text-lg mb-8 max-w-md mx-auto">
            Você não tem permissão para acessar esta página. Se acha que isso é um erro, entre em contato conosco.
        </p>

        <a href="/anuncios" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar para o início
        </a>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
