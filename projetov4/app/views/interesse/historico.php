<?php
$tituloPagina = 'Histórico de Candidaturas';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$candidaturas = $candidaturas ?? [];
$usuarioLogado = $usuarioLogado ?? null;
$isTrabalhador = $usuarioLogado && $usuarioLogado->isTrabalhador();
$isContratante = $usuarioLogado && $usuarioLogado->isContratante();
?>

<main class="flex-1">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Histórico</h1>

        <!-- Abas -->
        <div class="flex flex-wrap gap-2 mb-8 border-b border-gray-200">
            <button onclick="mostrarAba('minhas-candidaturas')" class="tab-btn active px-4 py-2 font-semibold text-blue-600 border-b-2 border-blue-600">
                Minhas Candidaturas
            </button>
            <?php if ($isContratante): ?>
                <button onclick="mostrarAba('meus-vagas')" class="tab-btn px-4 py-2 font-semibold text-gray-600 border-b-2 border-transparent hover:text-blue-600">
                    Meus Anúncios
                </button>
            <?php endif; ?>
        </div>

        <!-- ABA: Minhas Candidaturas -->
        <div id="minhas-candidaturas" class="tab-content">
            
            <!-- Filtro de Status -->
            <div class="flex flex-wrap gap-2 mb-6">
                <button class="px-4 py-2 rounded-full bg-blue-100 text-blue-800 font-medium hover:bg-blue-200 transition-colors" onclick="filtrarStatus('todos')">Todos</button>
                <button class="px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors" onclick="filtrarStatus('pendente')">Pendente</button>
                <button class="px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors" onclick="filtrarStatus('aceito')">Aceito</button>
                <button class="px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors" onclick="filtrarStatus('recusado')">Recusado</button>
            </div>

            <!-- Grid de Cards de Candidaturas -->
            <?php if (empty($candidaturas)): ?>
                <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p class="text-gray-500">Nenhuma candidatura ainda</p>
                    <a href="/vagas" class="text-blue-600 hover:text-blue-700 font-semibold mt-2 inline-block">Explorar vagas →</a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($candidaturas as $item):
                        $candidatura = $item['candidatura'];
                        $vaga = $item['vaga'];
                        $contratante = $item['contratante'];
                        $status = htmlspecialchars($candidatura->getStatus(), ENT_QUOTES, 'UTF-8');
                        $statusBadge = match($status) {
                            'ACEITO' => 'bg-green-100 text-green-800',
                            'RECUSADO' => 'bg-red-100 text-red-800',
                            default => 'bg-yellow-100 text-yellow-800'
                        };
                        $tituloVaga = $vaga ? $vaga->getTitulo() : 'Anúncio indisponível';
                        $nomeContratante = $contratante ? $contratante->getNome() : 'Contratante';
                    ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 candidatura-card status-<?= strtolower($status) ?>">
                            
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="font-bold text-gray-900"><?= htmlspecialchars($tituloVaga, ENT_QUOTES, 'UTF-8') ?></h3>
                                    <p class="text-sm text-gray-600">por <?= htmlspecialchars($nomeContratante, ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusBadge ?>">
                                    <?= ucfirst(strtolower($status)) ?>
                                </span>
                            </div>

                            <!-- Remuneração e Tipo -->
                            <div class="flex items-center justify-between mb-4 py-4 border-y border-gray-200">
                                <div>
                                    <p class="text-xs text-gray-600">Remuneração</p>
                                    <p class="text-lg font-bold text-green-600"><?= $vaga ? 'R$ ' . number_format($vaga->getRemuneracao(), 2, ',', '.') : 'N/D' ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-600">Tipo</p>
                                    <p class="font-semibold text-gray-900"><?= $vaga ? ($vaga->getTipoServico() === 'TEMPORARIO' ? 'Temporário' : 'Fixo') : 'N/D' ?></p>
                                </div>
                            </div>

                            <!-- Data e Localização -->
                            <div class="space-y-2 text-sm mb-4 text-gray-600">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>Candidatou-se em <?= htmlspecialchars(date('d/m/Y', strtotime($candidatura->getDataCandidatura())), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    <span><?= htmlspecialchars($vaga?->getLocalizacao() ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </div>

                            <!-- Botões -->
                            <div class="space-y-2">
                                <?php if ($vaga): ?>
                                    <a href="/vagas/visualizar?id=<?= $vaga->getIdVaga() ?>" class="block text-center bg-blue-50 hover:bg-blue-100 text-blue-600 font-medium py-2 rounded-lg text-sm transition-colors">
                                        Ver vaga
                                    </a>
                                <?php else: ?>
                                    <div class="block text-center bg-gray-50 text-gray-500 font-medium py-2 rounded-lg text-sm">
                                        Vaga indisponível
                                    </div>
                                <?php endif; ?>
                                <?php if ($status === 'ACEITO'): ?>
                                    <form method="POST" action="/candidatura/confirmar">
                                        <input type="hidden" name="id" value="<?= $candidatura->getIdCandidatura() ?>">
                                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 rounded-lg text-sm transition-colors">
                                            Confirmar interesse
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ABA: Meus Anúncios (Contratante) -->
        <?php if ($isContratante): ?>
            <div id="meus-vagas" class="tab-content hidden">
                <!-- Conteúdo específico para anúncios do contratante -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center">
                    <p class="text-gray-600">Gerencie seus anúncios em <a href="/vagas" class="text-blue-600 hover:text-blue-700 font-semibold">Minhas Vagas</a></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function mostrarAba(abaId) {
    // Ocultar todas as abas
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    
    // Remover classe ativa dos botões
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-blue-600', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-600', 'hover:text-blue-600');
    });
    
    // Mostrar aba selecionada
    document.getElementById(abaId).classList.remove('hidden');
    
    // Ativar botão selecionado
    event.target.classList.remove('border-transparent', 'text-gray-600', 'hover:text-blue-600');
    event.target.classList.add('border-blue-600', 'text-blue-600');
}

function filtrarStatus(status) {
    const cards = document.querySelectorAll('.candidatura-card');
    cards.forEach(card => {
        if (status === 'todos') {
            card.style.display = 'block';
        } else {
            card.style.display = card.classList.contains('status-' + status) ? 'block' : 'none';
        }
    });
}
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>
