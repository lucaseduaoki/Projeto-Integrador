<?php
$tituloPagina = 'Painel de Denúncias';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$denuncias = $denuncias ?? [];
$statusFiltro = $status ?? '';
$erroModeracao = $erroModeracao ?? '';

$pendentes = count(array_filter($denuncias, fn($denuncia) => $denuncia->getStatus() === 'PENDENTE'));
$analisadas = count(array_filter($denuncias, fn($denuncia) => $denuncia->getStatus() === 'ANALISADA'));
?>

<main class="flex-1">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Painel de Denúncias</h1>
            <p class="text-gray-600 mt-1">Gerencie as denúncias enviadas na plataforma</p>
        </div>

        <?php if ($erroModeracao !== ''): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm mb-6">
                <?= htmlspecialchars($erroModeracao, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-md border border-gray-100 p-6">
                <p class="text-sm text-gray-600">Pendentes</p>
                <p class="text-3xl font-bold text-yellow-600"><?= $pendentes ?></p>
            </div>
            <div class="bg-white rounded-md border border-gray-100 p-6">
                <p class="text-sm text-gray-600">Analisadas</p>
                <p class="text-3xl font-bold text-blue-600"><?= $analisadas ?></p>
            </div>
            <div class="bg-white rounded-md border border-gray-100 p-6">
                <p class="text-sm text-gray-600">Total</p>
                <p class="text-3xl font-bold text-gray-900"><?= count($denuncias) ?></p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 mb-6">
            <a href="<?= URL_BASE ?>/admin/denuncias" class="px-4 py-2 rounded-full <?= $statusFiltro === '' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700' ?> font-medium hover:bg-blue-200 transition-colors">
                Todas
            </a>
            <a href="<?= URL_BASE ?>/admin/denuncias?status=pendentes" class="px-4 py-2 rounded-full <?= $statusFiltro === 'pendentes' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700' ?> font-medium hover:bg-blue-200 transition-colors">
                Pendentes
            </a>
        </div>

        <?php if (empty($denuncias)): ?>
            <div class="text-center py-16 bg-white rounded-md border border-gray-100">
                <p class="text-gray-500 text-lg">Nenhuma denúncia encontrada</p>
                <p class="text-gray-400 text-sm mt-1">Quando surgirem denúncias, elas aparecerão aqui</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-md border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">ID</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Denunciante</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Denunciado</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Motivo</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Data</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($denuncias as $denuncia):
                                $statusDenuncia = $denuncia->getStatus();
                                $statusBadge = $statusDenuncia === 'PENDENTE'
                                    ? 'bg-yellow-100 text-yellow-800'
                                    : 'bg-blue-100 text-blue-800';
                            ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-700">#<?= $denuncia->getIdDenuncia() ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-700">Usuário #<?= $denuncia->getIdDenunciante() ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-700"><?= $denuncia->getIdVagaDenunciada() !== null && $denuncia->getIdUsuarioDenunciado() === null ? 'Anúncio #' . $denuncia->getIdVagaDenunciada() : 'Usuário #' . $denuncia->getIdUsuarioDenunciado() ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars(\app\models\Denuncia::rotuloMotivo($denuncia->getMotivo()), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($denuncia->getDataDenuncia())), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusBadge ?>">
                                            <?= $statusDenuncia === 'PENDENTE' ? 'Pendente' : 'Analisada' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php if ($statusDenuncia === 'PENDENTE'): ?>
                                            <div class="flex items-start gap-3">
                                                <details>
                                                    <summary class="cursor-pointer text-amber-700 hover:text-amber-800 font-medium text-xs">Advertir</summary>
                                                    <form method="POST" action="<?= URL_BASE ?>/admin/denuncias/moderar" class="mt-2 w-56 space-y-2">
                                                        <input type="hidden" name="id" value="<?= $denuncia->getIdDenuncia() ?>">
                                                        <input type="hidden" name="acao" value="advertir">
                                                        <textarea name="mensagem" required minlength="5" maxlength="500" rows="3" placeholder="Mensagem para o advertido" class="w-full border border-gray-300 rounded px-2 py-1 text-xs"></textarea>
                                                        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-medium text-xs py-1 px-2 rounded">Enviar advertência</button>
                                                    </form>
                                                </details>
                                                <?php if ($denuncia->getIdVagaDenunciada() !== null): ?>
                                                    <a href="<?= URL_BASE ?>/vagas/visualizar?id=<?= $denuncia->getIdVagaDenunciada() ?>" class="text-gray-600 hover:text-gray-900 font-medium text-xs">Ver anúncio</a>
                                                    <form method="POST" action="<?= URL_BASE ?>/admin/denuncias/moderar" class="inline">
                                                        <input type="hidden" name="id" value="<?= $denuncia->getIdDenuncia() ?>">
                                                        <input type="hidden" name="acao" value="ocultar">
                                                        <button type="submit" onclick="return confirm('Ocultar este anúncio? Ele some da listagem, mas o dono ainda pode vê-lo e corrigi-lo.')" class="text-orange-600 hover:text-orange-700 font-medium text-xs">Ocultar anúncio</button>
                                                    </form>
                                                    <form method="POST" action="<?= URL_BASE ?>/admin/denuncias/moderar" class="inline">
                                                        <input type="hidden" name="id" value="<?= $denuncia->getIdDenuncia() ?>">
                                                        <input type="hidden" name="acao" value="remover">
                                                        <button type="submit" onclick="return confirm('Remover este anúncio? O dono não poderá mais editá-lo nem excluí-lo.')" class="text-red-600 hover:text-red-700 font-medium text-xs">Remover anúncio</button>
                                                    </form>
                                                <?php endif; ?>
                                                <form method="POST" action="<?= URL_BASE ?>/admin/denuncias/moderar" class="inline">
                                                    <input type="hidden" name="id" value="<?= $denuncia->getIdDenuncia() ?>">
                                                    <input type="hidden" name="acao" value="bloquear">
                                                    <button type="submit" onclick="return confirm('Tem certeza que deseja bloquear a conta atingida por esta denúncia?')" class="text-red-600 hover:text-red-700 font-medium text-xs">Bloquear conta</button>
                                                </form>
                                                <form method="POST" action="<?= URL_BASE ?>/admin/denuncias/moderar" class="inline">
                                                    <input type="hidden" name="id" value="<?= $denuncia->getIdDenuncia() ?>">
                                                    <input type="hidden" name="acao" value="analisar">
                                                    <button type="submit" class="text-blue-600 hover:text-blue-700 font-medium text-xs">Analisar</button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>