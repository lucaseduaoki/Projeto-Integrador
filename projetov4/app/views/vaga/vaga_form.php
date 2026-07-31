<?php
$tituloPagina = isset($modoEdicao) && $modoEdicao ? 'Editar Vaga' : 'Publicar Nova Vaga';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$vaga = $vaga ?? null;
$erros = $erros ?? [];
$modoEdicao = $modoEdicao ?? false;
?>

<main class="flex-1">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">
                <?= $modoEdicao ? 'Editar Vaga' : 'Publicar Nova Vaga' ?>
            </h1>

            <!-- Erro Geral -->
            <?php if (isset($erros['geral'])): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">
                    <?= htmlspecialchars($erros['geral'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= $modoEdicao ? '/vagas/editar/submit' : '/vagas/criar/submit' ?>" class="space-y-6">
                
                <?php if ($modoEdicao): ?>
                    <input type="hidden" name="id_vaga" value="<?= $vaga->getIdVaga() ?>">
                <?php endif; ?>

                <!-- Título (col-span-2) -->
                <div>
                    <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">Título da vaga</label>
                    <input 
                        type="text" 
                        id="titulo" 
                        name="titulo" 
                        required
                        value="<?= $vaga ? htmlspecialchars($vaga->getTitulo(), ENT_QUOTES, 'UTF-8') : '' ?>"
                        placeholder="Ex: Pedreiro para reformas residenciais"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['titulo']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
                    >
                    <?php if (isset($erros['titulo'])): ?>
                        <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['titulo'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Descrição (col-span-2) -->
                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição detalhada da vaga</label>
                    <textarea 
                        id="descricao" 
                        name="descricao" 
                        required
                        rows="5"
                        placeholder="Descreva em detalhes as responsabilidades, requisitos e diferenciais..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['descricao']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
                    ><?= $vaga ? htmlspecialchars($vaga->getDescricao(), ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                    <?php if (isset($erros['descricao'])): ?>
                        <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['descricao'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Grid dois em um -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Localização -->
                    <div>
                        <label for="localizacao" class="block text-sm font-medium text-gray-700 mb-1">Localização</label>
                        <input 
                            type="text" 
                            id="localizacao" 
                            name="localizacao" 
                            required
                            value="<?= $vaga ? htmlspecialchars($vaga->getLocalizacao(), ENT_QUOTES, 'UTF-8') : '' ?>"
                            placeholder="Cidade, Estado"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['localizacao']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
                        >
                        <?php if (isset($erros['localizacao'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['localizacao'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Remuneração -->
                    <div>
                        <label for="remuneracao" class="block text-sm font-medium text-gray-700 mb-1">Remuneração (R$)</label>
                        <input 
                            type="number" 
                            id="remuneracao" 
                            name="remuneracao" 
                            required
                            step="0.01"
                            min="0"
                            value="<?= $vaga ? number_format($vaga->getRemuneracao(), 2, '.', '') : '' ?>"
                            placeholder="0.00"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['remuneracao']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
                        >
                        <?php if (isset($erros['remuneracao'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['remuneracao'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Tipo de Serviço -->
                    <div>
                        <label for="tipo_servico" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Serviço</label>
                        <select 
                            id="tipo_servico" 
                            name="tipo_servico" 
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['tipo_servico']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
                        >
                            <option value="">-- Selecionar --</option>
                            <option value="TEMPORARIO" <?= $vaga && $vaga->getTipoServico() === 'TEMPORARIO' ? 'selected' : '' ?>>Temporário</option>
                            <option value="FIXO" <?= $vaga && $vaga->getTipoServico() === 'FIXO' ? 'selected' : '' ?>>Fixo</option>
                        </select>
                        <?php if (isset($erros['tipo_servico'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['tipo_servico'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Duração -->
                    <div>
                        <label for="duracao" class="block text-sm font-medium text-gray-700 mb-1">Duração estimada</label>
                        <input 
                            type="text" 
                            id="duracao" 
                            name="duracao" 
                            value="<?= $vaga ? htmlspecialchars($vaga->getDuracao(), ENT_QUOTES, 'UTF-8') : '' ?>"
                            placeholder="Ex: 4 horas, 1 semana"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>

                    <!-- Data e Horário -->
                    <div>
                        <label for="data" class="block text-sm font-medium text-gray-700 mb-1">Data e horário</label>
                        <input 
                            type="datetime-local" 
                            id="data" 
                            name="data" 
                            required
                            value="<?= $vaga ? htmlspecialchars($vaga->getData(), ENT_QUOTES, 'UTF-8') : '' ?>"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['data']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
                        >
                        <?php if (isset($erros['data'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['data'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Prazo para Candidatura -->
                    <div>
                        <label for="prazo_candidatura" class="block text-sm font-medium text-gray-700 mb-1">Prazo para candidatura <span class="text-gray-500">(opcional)</span></label>
                        <input 
                            type="date" 
                            id="prazo_candidatura" 
                            name="prazo_candidatura" 
                            value="<?= $vaga && $vaga->getPrazoCandidatura() ? htmlspecialchars($vaga->getPrazoCandidatura(), ENT_QUOTES, 'UTF-8') : '' ?>"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                </div>

                <!-- Observações (col-span-2) -->
                <div>
                    <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-1">Observações adicionais <span class="text-gray-500">(opcional)</span></label>
                    <textarea 
                        id="observacoes" 
                        name="observacoes" 
                        rows="3"
                        placeholder="Informações extras que sejam relevantes..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    ><?= $vaga ? htmlspecialchars($vaga->getObservacoes() ?? '', ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                </div>

                <!-- Botões -->
                <div class="flex gap-4 pt-6 border-t border-gray-200">
                    <button 
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-200"
                    >
                        <?= $modoEdicao ? 'Salvar alterações' : 'Publicar vaga' ?>
                    </button>
                    <a 
                        href="/vagas"
                        class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-6 rounded-lg transition-colors duration-200"
                    >
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
