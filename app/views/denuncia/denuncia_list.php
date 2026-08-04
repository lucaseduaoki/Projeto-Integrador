<?php
$tituloPagina = 'Painel de Denúncias';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$denuncias = $denuncias ?? [];
?>

<main class="flex-1">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Painel de Denúncias</h1>
            <p class="text-gray-600 mt-1">Gerencie e modere denúncias de usuários</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Pendentes</p>
                        <p class="text-3xl font-bold text-yellow-600">
                            <?= count(array_filter($denuncias, fn($d) => $d->getStatus() === 'PENDENTE')) ?>
                        </p>
                    </div>
                    <svg class="w-12 h-12 text-yellow-600 opacity-20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Analisadas</p>
                        <p class="text-3xl font-bold text-blue-600">
                            <?= count(array_filter($denuncias, fn($d) => $d->getStatus() === 'ANALISADO')) ?>
                        </p>
                    </div>
                    <svg class="w-12 h-12 text-blue-600 opacity-20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total</p>
                        <p class="text-3xl font-bold text-gray-900"><?= count($denuncias) ?></p>
                    </div>
                    <svg class="w-12 h-12 text-gray-900 opacity-20" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Abas -->
        <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200">
            <button class="tab-btn active px-4 py-2 font-semibold text-blue-600 border-b-2 border-blue-600">Pendentes</button>
            <button class="tab-btn px-4 py-2 font-semibold text-gray-600 border-b-2 border-transparent">Analisadas</button>
            <button class="tab-btn px-4 py-2 font-semibold text-gray-600 border-b-2 border-transparent">Todas</button>
        </div>

        <!-- Tabela -->
        <?php if (empty($denuncias)): ?>
            <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
                <p class="text-gray-500">Nenhuma denúncia no momento</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
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
                                $status = htmlspecialchars($denuncia->getStatus(), ENT_QUOTES, 'UTF-8');
                                $motivo = htmlspecialchars($denuncia->getMotivo(), ENT_QUOTES, 'UTF-8');
                                $statusBadge = $status === 'PENDENTE' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800';
                            ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                                                <?= htmlspecialchars(strtoupper(substr($denuncia->getDenunciante()->getNome(), 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <span class="font-medium text-gray-900"><?= htmlspecialchars($denuncia->getDenunciante()->getNome(), ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-red-600 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                                                <?= htmlspecialchars(strtoupper(substr($denuncia->getDenunciado()->getNome(), 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <span class="font-medium text-gray-900"><?= htmlspecialchars($denuncia->getDenunciado()->getNome(), ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?= $motivo ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?= htmlspecialchars(date('d/m/Y H:i', strtotime($denuncia->getData())), ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusBadge ?>">
                                            <?= $status === 'PENDENTE' ? 'Pendente' : 'Analisada' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php if ($status === 'PENDENTE'): ?>
                                            <div class="flex items-center gap-2">
                                                <form method="POST" action="/admin/denuncias/moderar" class="inline">
                                                    <input type="hidden" name="id_denuncia" value="<?= $denuncia->getId() ?>">
                                                    <input type="hidden" name="acao" value="bloquear">
                                                    <button type="submit" onclick="return confirm('Tem certeza que deseja bloquear este usuário?')" class="text-red-600 hover:text-red-700 font-medium text-xs">Bloquear</button>
                                                </form>
                                                <span class="text-gray-300">|</span>
                                                <form method="POST" action="/admin/denuncias/moderar" class="inline">
                                                    <input type="hidden" name="id_denuncia" value="<?= $denuncia->getId() ?>">
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

<script>
// Simples sistema de abas (pode ser expandido)
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('border-blue-600', 'text-blue-600');
            b.classList.add('border-transparent', 'text-gray-600');
        });
        this.classList.add('border-blue-600', 'text-blue-600');
        this.classList.remove('border-transparent', 'text-gray-600');
    });
});
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>
