<?php
$tituloPagina = 'Vagas Disponíveis';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$anuncios = $anuncios ?? [];
$usuarioLogado = $usuarioLogado ?? null;
$isContratante = $usuarioLogado && $usuarioLogado->isContratante();

error_log('[ANUNCIO_LIST] Anúncios recebidos: ' . count($anuncios) . ', tipo: ' . gettype($anuncios));
if (!empty($anuncios)) {
    error_log('[ANUNCIO_LIST] Primeiro anúncio: ' . get_class($anuncios[0]));
    error_log('[ANUNCIO_LIST] Título: ' . $anuncios[0]->getTitulo());
}
?>

<main class="flex-1">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Vagas disponíveis</h1>
                <p class="text-gray-600 mt-1"><?= count($anuncios) ?> vagas</p>
            </div>
            
            <?php if ($isContratante): ?>
                <a href="/anuncios/criar" class="mt-4 sm:mt-0 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-200">
                    + Publicar vaga
                </a>
            <?php endif; ?>
        </div>

        <!-- Filtros Rápidos -->
        <div class="flex flex-wrap gap-2 mb-8">
            <a href="/anuncios" class="px-4 py-2 rounded-full bg-blue-100 text-blue-800 font-medium hover:bg-blue-200 transition-colors">Todos</a>
            <a href="/anuncios/buscar" class="px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors">Temporário</a>
            <a href="/anuncios/buscar" class="px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors">Fixo</a>
        </div>

        <!-- Grid de Anúncios -->
        <?php if (empty($anuncios)): ?>
            <div class="text-center py-16">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-gray-500">Nenhuma vaga encontrada</p>
                <p class="text-gray-400 text-sm mt-1">Volte em breve para mais oportunidades</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($anuncios as $anuncio): 
                    $titulo = htmlspecialchars($anuncio->getTitulo(), ENT_QUOTES, 'UTF-8');
                    $descricao = htmlspecialchars(substr($anuncio->getDescricao(), 0, 100) . '...', ENT_QUOTES, 'UTF-8');
                    $localizacao = htmlspecialchars($anuncio->getLocalizacao(), ENT_QUOTES, 'UTF-8');
                    $remuneracao = number_format($anuncio->getRemuneracao(), 2, ',', '.');
                    $tipo = htmlspecialchars($anuncio->getTipoServico(), ENT_QUOTES, 'UTF-8');
                    $status = htmlspecialchars($anuncio->getStatus(), ENT_QUOTES, 'UTF-8');
                    $statusBadge = $status === 'ABERTO' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                ?>
                    <a href="/anuncios/visualizar?id=<?= $anuncio->getId() ?>" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:-translate-y-1 transition-all duration-200 cursor-pointer">
                        
                        <!-- Status -->
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusBadge ?>">
                                <?= $status === 'ABERTO' ? '✓ Aberto' : 'Encerrado' ?>
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <?= $tipo === 'TEMPORARIO' ? 'Temporário' : 'Fixo' ?>
                            </span>
                        </div>

                        <!-- Título -->
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= $titulo ?></h3>

                        <!-- Descrição -->
                        <p class="text-sm text-gray-600 mb-4"><?= $descricao ?></p>

                        <!-- Detalhes -->
                        <div class="space-y-2 mb-4 text-sm text-gray-600">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span><?= $localizacao ?></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="font-semibold text-green-600">R$ <?= $remuneracao ?></span>
                            </div>
                        </div>

                        <!-- Botão -->
                        <div class="pt-4 border-t border-gray-100">
                            <span class="text-blue-600 font-semibold text-sm">Ver detalhes →</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
