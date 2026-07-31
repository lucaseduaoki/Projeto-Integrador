<?php
$tituloPagina = 'Vagas Disponíveis';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$vagas = $vagas ?? [];
$usuarioLogado = $usuario ?? null;

$isContratante = $usuarioLogado &&
    $usuarioLogado->getTipoUsuario() === 'CONTRATANTE';

error_log('[ANUNCIO_LIST] Anúncios recebidos: ' . count($vagas));

if (!empty($vagas)) {
    error_log('[ANUNCIO_LIST] Primeiro anúncio: ' . get_class($vagas[0]));
    error_log('[ANUNCIO_LIST] Título: ' . $vagas[0]->getTitulo());
}
?>

<main class="flex-1">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">

            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Vagas disponíveis
                </h1>

                <p class="text-gray-600 mt-1">
                    <?= count($vagas) ?> vagas
                </p>
            </div>

            <?php if ($isContratante): ?>
                <a
                    href="/vagas/criar"
                    class="mt-4 sm:mt-0 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                    + Publicar vaga
                </a>
            <?php endif; ?>

        </div>

        <?php if (empty($vagas)): ?>

            <div class="text-center py-16">

                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>

                <p class="text-gray-500">
                    Nenhuma vaga encontrada
                </p>

                <p class="text-gray-400 text-sm mt-1">
                    Volte em breve para mais oportunidades.
                </p>

            </div>

        <?php else: ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                <?php foreach ($vagas as $vaga):

                    $titulo = htmlspecialchars(
                        $vaga->getTitulo(),
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    $descricao = htmlspecialchars(
                        mb_strimwidth($vaga->getDescricao(), 0, 120, "..."),
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    $localizacao = htmlspecialchars(
                        $vaga->getLocalizacao() ?? 'Não informado',
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    $remuneracao = $vaga->getRemuneracao() !== null
                        ? 'R$ ' . number_format($vaga->getRemuneracao(), 2, ',', '.')
                        : 'A combinar';

                    $status = $vaga->getStatus();

                    $statusBadge = $status === 'ATIVA'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-gray-100 text-gray-700';

                ?>

                    <a
                        href="/vagas/visualizar?id=<?= $vaga->getIdVaga() ?>"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:-translate-y-1 transition-all duration-200">

                        <div class="flex justify-between items-center mb-3">

                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $statusBadge ?>">
                                <?= $status === 'ATIVA' ? '✓ Ativa' : 'Encerrada' ?>
                            </span>

                            <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                                Categoria #<?= $vaga->getIdCategoria() ?>
                            </span>

                        </div>

                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            <?= $titulo ?>
                        </h3>

                        <p class="text-sm text-gray-600 mb-5">
                            <?= $descricao ?>
                        </p>

                        <div class="space-y-2 text-sm text-gray-600">

                            <div class="flex items-center gap-2">

                                <svg class="w-4 h-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>

                                </svg>

                                <?= $localizacao ?>

                            </div>

                            <div class="flex items-center gap-2">

                                <svg class="w-4 h-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>

                                </svg>

                                <span class="font-semibold text-green-600">
                                    <?= $remuneracao ?>
                                </span>

                            </div>

                            <?php if ($vaga->getDataLimite()): ?>

                                <div class="text-xs text-gray-500 pt-2">
                                    Inscrições até
                                    <?= date('d/m/Y', strtotime($vaga->getDataLimite())) ?>
                                </div>

                            <?php endif; ?>

                        </div>

                        <div class="pt-4 mt-4 border-t border-gray-100">

                            <span class="text-blue-600 font-semibold text-sm">
                                Ver detalhes →
                            </span>

                        </div>

                    </a>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>