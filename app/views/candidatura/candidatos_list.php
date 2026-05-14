<?php
$tituloPagina = 'Candidatos da Vaga';

$anuncio = $anuncio ?? null;
$candidaturas = $candidaturas ?? ($candidatos ?? []);

$tituloVaga = $anuncio && method_exists($anuncio, 'getTitulo')
    ? $anuncio->getTitulo()
    : 'Vaga selecionada';
$voltarUrl = $anuncio ? '/anuncios/visualizar?id=' . $anuncio->getId() : '/anuncios';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';
?>

<main class="flex-1">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <a href="<?= htmlspecialchars($voltarUrl, ENT_QUOTES, 'UTF-8') ?>" class="text-blue-600 hover:text-blue-700 font-medium text-sm mb-4 inline-block">← Voltar para vagas</a>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Candidatos para:</h1>
                    <p class="text-xl text-gray-700 mt-1"><?= htmlspecialchars($tituloVaga, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    <?= count($candidaturas) ?> candidato<?= count($candidaturas) !== 1 ? 's' : '' ?>
                </span>
            </div>
        </div>

        <?php if (empty($candidaturas)): ?>
            <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                <p class="text-gray-500 text-lg">Nenhum candidato ainda</p>
                <p class="text-gray-400 text-sm mt-1">Candidatos aparecerão aqui quando se inscreverem</p>
            </div>
        <?php else: ?>
            <div class="hidden md:block bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Candidato</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Data</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($candidaturas as $item):
                            $candidatura = is_array($item) ? ($item['candidatura'] ?? null) : $item;
                            if (!$candidatura) {
                                continue;
                            }

                            $trabalhador = is_array($item) ? ($item['trabalhador'] ?? null) : (method_exists($candidatura, 'getTrabalhador') ? $candidatura->getTrabalhador() : null);
                            $nomeTrabalhador = $trabalhador ? $trabalhador->getNome() : ('Trabalhador #' . $candidatura->getIdTrabalhador());
                            $emailTrabalhador = $trabalhador ? $trabalhador->getEmail() : 'ID do trabalhador: ' . $candidatura->getIdTrabalhador();
                            $status = htmlspecialchars($candidatura->getStatus(), ENT_QUOTES, 'UTF-8');
                            $statusBadge = match ($status) {
                                'ACEITO' => 'bg-green-100 text-green-800',
                                'RECUSADO' => 'bg-red-100 text-red-800',
                                default => 'bg-yellow-100 text-yellow-800',
                            };
                        ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold flex-shrink-0">
                                            <?= htmlspecialchars(strtoupper(substr($nomeTrabalhador, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900"><?= htmlspecialchars($nomeTrabalhador, ENT_QUOTES, 'UTF-8') ?></p>
                                            <p class="text-xs text-gray-600"><?= htmlspecialchars($emailTrabalhador, ENT_QUOTES, 'UTF-8') ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= htmlspecialchars(date('d/m/Y', strtotime($candidatura->getDataCandidatura())), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusBadge ?>">
                                        <?= ucfirst(strtolower($status)) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <?php if ($status === 'PENDENTE'): ?>
                                            <form method="POST" action="/candidatura/selecionar" class="inline">
                                                <input type="hidden" name="id" value="<?= $candidatura->getIdCandidatura() ?>">
                                                <button type="submit" class="text-green-600 hover:text-green-700 font-medium">Selecionar</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="md:hidden space-y-4">
                <?php foreach ($candidaturas as $item):
                    $candidatura = is_array($item) ? ($item['candidatura'] ?? null) : $item;
                    if (!$candidatura) {
                        continue;
                    }

                    $trabalhador = is_array($item) ? ($item['trabalhador'] ?? null) : (method_exists($candidatura, 'getTrabalhador') ? $candidatura->getTrabalhador() : null);
                    $nomeTrabalhador = $trabalhador ? $trabalhador->getNome() : ('Trabalhador #' . $candidatura->getIdTrabalhador());
                    $emailTrabalhador = $trabalhador ? $trabalhador->getEmail() : 'ID do trabalhador: ' . $candidatura->getIdTrabalhador();
                    $status = htmlspecialchars($candidatura->getStatus(), ENT_QUOTES, 'UTF-8');
                    $statusBadge = match ($status) {
                        'ACEITO' => 'bg-green-100 text-green-800',
                        'RECUSADO' => 'bg-red-100 text-red-800',
                        default => 'bg-yellow-100 text-yellow-800',
                    };
                ?>
                    <div class="bg-white rounded-lg border border-gray-100 p-4">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold flex-shrink-0">
                                <?= htmlspecialchars(strtoupper(substr($nomeTrabalhador, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900"><?= htmlspecialchars($nomeTrabalhador, ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="text-xs text-gray-600"><?= htmlspecialchars($emailTrabalhador, ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusBadge ?>">
                                <?= ucfirst(strtolower($status)) ?>
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">Candidatou-se em <?= htmlspecialchars(date('d/m/Y', strtotime($candidatura->getDataCandidatura())), ENT_QUOTES, 'UTF-8') ?></p>
                        <div class="flex gap-2">
                            <?php if ($status === 'PENDENTE'): ?>
                                <form method="POST" action="/candidatura/selecionar" class="flex-1">
                                    <input type="hidden" name="id" value="<?= $candidatura->getIdCandidatura() ?>">
                                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 rounded-lg text-sm">Selecionar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
