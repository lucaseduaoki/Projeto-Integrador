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

            <div class="bg-white rounded-xl border p-10 text-center">

                <p class="text-gray-500 text-lg">
                    Você ainda não demonstrou interesse em nenhuma vaga.
                </p>

                <a
                    href="/vagas"
                    class="inline-block mt-5 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">

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
                        'ACEITO' => 'bg-green-100 text-green-700',
                        'RECUSADO' => 'bg-red-100 text-red-700',
                        default => 'bg-yellow-100 text-yellow-700'
                    };

                ?>

                    <div class="bg-white border rounded-xl p-6 shadow-sm">

                        <div class="flex justify-between items-start">

                            <div>

                                <h2 class="font-bold text-lg">

                                    <?= htmlspecialchars($vaga ? $vaga->getTitulo() : 'Vaga removida') ?>

                                </h2>

                                <p class="text-gray-500 text-sm mt-1">

                                    <?= htmlspecialchars($contratante ? $contratante->getNome() : 'Contratante não encontrado') ?>

                                </p>

                            </div>

                            <span class="<?= $badge ?> px-3 py-1 rounded-full text-xs font-semibold">

                                <?= htmlspecialchars($status) ?>

                            </span>

                        </div>

                        <?php if ($vaga): ?>

                            <p class="text-gray-600 mt-4">

                                <?= htmlspecialchars($vaga->getDescricao()) ?>

                            </p>

                            <div class="mt-5 space-y-2 text-sm">

                                <div>

                                    <strong>Local:</strong>

                                    <?= htmlspecialchars($vaga->getLocalizacao()) ?>

                                </div>

                                <div>

                                    <strong>Remuneração:</strong>

                                    R$ <?= number_format($vaga->getRemuneracao(), 2, ',', '.') ?>

                                </div>

                                <div>

                                    <strong>Data da candidatura:</strong>

                                    <?= date(
                                        'd/m/Y H:i',
                                        strtotime($candidatura->getDataInteresse())
                                    ) ?>

                                </div>

                            </div>

                            <a
                                href="/vagas/visualizar?id=<?= $vaga->getIdVaga() ?>"
                                class="block text-center mt-6 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg">

                                Ver vaga

                            </a>

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