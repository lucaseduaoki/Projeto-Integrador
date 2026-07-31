<?php
$denunciado = $denunciado ;
$erros = $erros ?? [];
$tituloPagina = 'Realizar Denúncia';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';
?>

<main class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg">
        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-8 bg-red-50">
            
            <!-- Header com Alerta -->
            <div class="flex items-start gap-3 mb-6 pb-6 border-b border-red-200">
                <svg class="w-8 h-8 text-red-600 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h1 class="text-2xl font-bold text-red-900">Realizar Denúncia</h1>
                    <p class="text-red-800 text-sm mt-1">Denúncias falsas podem resultar em suspensão da sua conta</p>
                </div>
            </div>

            <!-- Avatar e Nome do Denunciado -->
            <div class="text-center mb-8 pb-8 border-b border-red-200">
                <div class="w-20 h-20 rounded-full bg-red-600 text-white flex items-center justify-center text-3xl font-bold mx-auto mb-3">
                    <?= htmlspecialchars(strtoupper(substr($denunciado->getNome(), 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <h2 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($denunciado->getNome(), ENT_QUOTES, 'UTF-8') ?></h2>
            </div>

            <!-- Erro Geral -->
            <?php if (isset($erros['geral'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">
                    <?= htmlspecialchars($erros['geral'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/denuncia/criar/submit" class="space-y-6">
                
                <input type="hidden" name="id_usuario_denunciado" value="<?= $denunciado->getIdUsuario() ?>">

                <!-- Motivo -->
                <div>
                    <label for="motivo" class="block text-sm font-medium text-gray-700 mb-1">Motivo da denúncia <span class="text-red-600">*</span></label>
                    <select 
                        id="motivo" 
                        name="motivo" 
                        required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['motivo']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
                    >
                        <option value="">-- Selecionar motivo --</option>
                        <option value="Comportamento inapropriado">Comportamento inapropriado</option>
                        <option value="Informações falsas">Informações falsas</option>
                        <option value="Não comparecimento">Não comparecimento</option>
                        <option value="Assédio">Assédio</option>
                        <option value="Outro">Outro</option>
                    </select>
                    <?php if (isset($erros['motivo'])): ?>
                        <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['motivo'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Descrição Detalhada -->
                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição detalhada</label>
                    <textarea 
                        id="descricao" 
                        name="descricao" 
                        rows="5"
                        placeholder="Descreva o ocorrido com o máximo de detalhes possível. Isso nos ajuda a investigar melhor."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['descricao']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
                    ></textarea>
                    <?php if (isset($erros['descricao'])): ?>
                        <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['descricao'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Aviso de Consentimento -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-800">
                        <strong>⚠️ Importante:</strong> Todas as denúncias são revisadas manualmente pela nossa equipe de moderação. Denúncias falsas ou infundadas podem resultar em ação contra sua conta.
                    </p>
                </div>

                <!-- Botões -->
                <div class="flex gap-4 pt-6 border-t border-gray-200">
                    <button 
                        type="submit"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200"
                    >
                        Enviar Denúncia
                    </button>
                    <a 
                        href="/vagas"
                        class="flex-1 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg text-center transition-colors duration-200"
                    >
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
