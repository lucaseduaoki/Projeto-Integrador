<?php

$tituloPagina = 'Histórico de Candidaturas';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$interesses = $interesses ?? [];
?>

<main class="flex-1">
    <div class="max-w-7xl mx-auto px-4 py-8">

        <h1 class="text-3xl font-bold mb-8">
            Histórico de Candidaturas
        </h1>

        <?php if (empty($interesses)): ?>

            <div class="bg-white rounded-md border p-10 text-center">

                <p class="text-gray-500 text-lg">
                    Você ainda não demonstrou interesse em nenhuma vaga.
                </p>

                <a
                    href="<?= URL_BASE ?>/vagas"
                    class="inline-block mt-5 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded">

                    Buscar vagas

                </a>

            </div>

        <?php else: ?>

            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">

                <?php foreach ($interesses as $item):

                    $candidatura = $item['candidatura'];
                    $vaga = $item['vaga'];
                    $contratante = $item['contratante'];

                    $status = $candidatura->getStatus();

                    $badge = match ($status) {
                        'ACEITO' => 'bg-green-100 text-green-800',
                        'RECUSADO' => 'bg-red-100 text-red-700',
                        default => 'bg-yellow-100 text-yellow-700'
                    };

                    $statusLabel = match ($status) {
                        'ACEITO' => 'Aceito',
                        'RECUSADO' => 'Recusado',
                        default => 'Pendente'
                    };

                ?>

                    <div class="bg-white rounded-md border border-gray-100 p-6 hover:border-blue-500 transition-colors flex flex-col">

                        <div class="flex justify-between items-center mb-3">

                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $badge ?>">
                                <?= $statusLabel ?>
                            </span>

                            <?php if ($vaga && $vaga->getCategoriaNome()): ?>
                                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                                    <?= htmlspecialchars($vaga->getCategoriaNome(), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>

                        </div>

                        <h3 class="text-lg font-semibold text-gray-900 mb-1">
                            <?= htmlspecialchars($vaga ? $vaga->getTitulo() : 'Vaga removida') ?>
                        </h3>

                        <p class="text-sm text-gray-500 mb-5">
                            <?= htmlspecialchars($contratante ? $contratante->getNome() : 'Contratante não encontrado') ?>
                        </p>

                        <?php if ($vaga): ?>

                            <p class="text-sm text-gray-600 mb-5">
                                <?= htmlspecialchars(mb_strimwidth($vaga->getDescricao(), 0, 120, '...'), ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <div class="space-y-2 text-sm text-gray-600 flex-1">

                                <div class="flex items-center gap-2">

                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>

                                    <?= htmlspecialchars($vaga->getLocalizacao() ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>

                                </div>

                                <div class="flex items-center gap-2">

                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>

                                    <span class="font-semibold text-green-600">
                                        R$ <?= number_format($vaga->getRemuneracao(), 2, ',', '.') ?>
                                    </span>

                                </div>

                                <div class="text-xs text-gray-500 pt-2">
                                    Candidatura em
                                    <?= date('d/m/Y H:i', strtotime($candidatura->getDataCandidatura())) ?>
                                </div>

                            </div>

                            <div class="pt-4 mt-4 border-t border-gray-100">

                                <a
                                    href="<?= URL_BASE ?>/vagas/visualizar?id=<?= $vaga->getIdVaga() ?>"
                                    class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm py-2.5 rounded-md transition-colors">

                                    Ver vaga

                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>

                                </a>

                            </div>

                        <?php else: ?>

                            <div class="mt-5 text-red-600 text-sm">
                                Esta vaga foi removida.
                            </div>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>