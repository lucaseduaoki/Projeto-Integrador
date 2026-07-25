<?php
$tituloPagina = 'Buscar Vagas';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$anuncios = $anuncios ?? [];
$filtros = $filtros ?? [];
$totalResultados = $totalResultados ?? count($anuncios);
?>

<main class="flex-1">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Buscar Vagas</h1>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Sidebar Filtros (1/4) -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Filtros</h3>
                    
                    <form method="GET" action="/anuncios/buscar" class="space-y-6">
                        
                        <!-- Palavras-chave -->
                        <div>
                            <label for="keywords" class="block text-sm font-medium text-gray-700 mb-1">Palavras-chave</label>
                            <input 
                                type="text" 
                                id="keywords" 
                                name="keywords" 
                                value="<?= htmlspecialchars($filtros['keywords'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="Ex: pedreiro"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                            >
                        </div>

                        <!-- Localização -->
                        <div>
                            <label for="localizacao" class="block text-sm font-medium text-gray-700 mb-1">Localização</label>
                            <input 
                                type="text" 
                                id="localizacao" 
                                name="localizacao" 
                                value="<?= htmlspecialchars($filtros['localizacao'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="Cidade"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                            >
                        </div>

                        <!-- Tipo de Serviço -->
                        <div>
                            <label for="tipo_servico" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Serviço</label>
                            <select 
                                id="tipo_servico" 
                                name="tipo_servico" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                            >
                                <option value="">Todos</option>
                                <option value="TEMPORARIO" <?= isset($filtros['tipo_servico']) && $filtros['tipo_servico'] === 'TEMPORARIO' ? 'selected' : '' ?>>Temporário</option>
                                <option value="FIXO" <?= isset($filtros['tipo_servico']) && $filtros['tipo_servico'] === 'FIXO' ? 'selected' : '' ?>>Fixo</option>
                            </select>
                        </div>

                        <!-- Remuneração Mínima -->
                        <div>
                            <label for="remuneracao_min" class="block text-sm font-medium text-gray-700 mb-1">Remuneração Mínima (R$)</label>
                            <input 
                                type="number" 
                                id="remuneracao_min" 
                                name="remuneracao_min" 
                                value="<?= htmlspecialchars($filtros['remuneracao_min'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="0.00"
                                step="0.01"
                                min="0"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                            >
                        </div>

                        <!-- Remuneração Máxima -->
                        <div>
                            <label for="remuneracao_max" class="block text-sm font-medium text-gray-700 mb-1">Remuneração Máxima (R$)</label>
                            <input 
                                type="number" 
                                id="remuneracao_max" 
                                name="remuneracao_max" 
                                value="<?= htmlspecialchars($filtros['remuneracao_max'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="999999.99"
                                step="0.01"
                                min="0"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                            >
                        </div>

                        <!-- Data A Partir De -->
                        <div>
                            <label for="data_from" class="block text-sm font-medium text-gray-700 mb-1">Data a partir de</label>
                            <input 
                                type="date" 
                                id="data_from" 
                                name="data_from" 
                                value="<?= htmlspecialchars($filtros['data_from'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                            >
                        </div>

                        <!-- Botões -->
                        <div class="space-y-2">
                            <button 
                                type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200"
                            >
                                Aplicar Filtros
                            </button>
                            <a 
                                href="/anuncios/buscar"
                                class="block text-center text-gray-600 hover:text-gray-900 font-medium text-sm"
                            >
                                Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resultados (3/4) -->
            <div class="lg:col-span-3">
                
                <!-- Header com Ordenação -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">
                        <?= $totalResultados ?> vaga<?= $totalResultados !== 1 ? 's' : '' ?> encontrada<?= $totalResultados !== 1 ? 's' : '' ?>
                    </h2>
                    <select class="mt-4 sm:mt-0 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        <option>Mais recentes</option>
                        <option>Maior salário</option>
                        <option>Menor salário</option>
                    </select>
                </div>

                <!-- Grid de Resultados -->
                <?php if (empty($anuncios)): ?>
                    <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="text-gray-500 text-lg">Nenhuma vaga encontrada para sua busca</p>
                        <p class="text-gray-400 text-sm mt-1">Tente ajustar seus filtros</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($anuncios as $anuncio): 
                            $titulo = htmlspecialchars($anuncio->getTitulo(), ENT_QUOTES, 'UTF-8');
                            $descricao = htmlspecialchars(substr($anuncio->getDescricao(), 0, 100) . '...', ENT_QUOTES, 'UTF-8');
                            $localizacao = htmlspecialchars($anuncio->getLocalizacao(), ENT_QUOTES, 'UTF-8');
                            $remuneracao = number_format($anuncio->getRemuneracao(), 2, ',', '.');
                            $tipo = htmlspecialchars($anuncio->getTipoServico(), ENT_QUOTES, 'UTF-8');
                        ?>
                            <a href="/anuncios/visualizar?id=<?= $anuncio->getId() ?>" class="bg-white rounded-lg border border-gray-100 p-6 hover:shadow-md hover:border-blue-200 transition-all duration-200 cursor-pointer flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <?= $tipo === 'TEMPORARIO' ? 'Temporário' : 'Fixo' ?>
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900"><?= $titulo ?></h3>
                                    <p class="text-sm text-gray-600 mt-1"><?= $descricao ?></p>
                                    <div class="flex flex-wrap gap-4 mt-3 text-sm text-gray-600">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                            <?= $localizacao ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-green-600">R$ <?= $remuneracao ?></p>
                                    <span class="text-blue-600 font-semibold text-sm">Ver detalhes →</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
