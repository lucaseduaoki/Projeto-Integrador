<?php
$tituloPagina = 'Cadastro';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$erros = $erros ?? [];
$tipoUsuario = $_POST['tipo_usuario'] ?? 'TRABALHADOR';
?>

<main class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-5xl grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Left Column - Ilustração -->
        <div class="hidden lg:flex flex-col justify-center bg-gradient-to-br from-purple-600 to-blue-600 rounded-xl p-12 text-white">
            <h2 class="text-3xl font-bold mb-6">Comece sua jornada agora</h2>
            
            <div class="space-y-4">
                <div class="flex items-start gap-4">
                    <svg class="w-6 h-6 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-semibold">Perfil verificado</p>
                        <p class="text-purple-100 text-sm">Sua identidade é importante para nós.</p>
                    </div>
                </div>
                
                <div class="flex items-start gap-4">
                    <svg class="w-6 h-6 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-semibold">Proteção garantida</p>
                        <p class="text-purple-100 text-sm">Seus dados são protegidos com criptografia.</p>
                    </div>
                </div>
                
                <div class="flex items-start gap-4">
                    <svg class="w-6 h-6 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-semibold">Comunidade ativa</p>
                        <p class="text-purple-100 text-sm">Faça parte de uma rede confiável.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Formulário -->
        <div class="flex items-center">
            <div class="w-full bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-h-[90vh] overflow-y-auto">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Criar nova conta</h1>
                <p class="text-gray-600 mb-6">Cadastre-se em minutos e comece agora.</p>

                <!-- Erro Geral -->
                <?php if (isset($erros['geral'])): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">
                        <?= htmlspecialchars($erros['geral'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/cadastro/submit" class="space-y-4">
                    

                    <!-- Nome Completo -->
                    <div>
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome completo</label>
                        <input 
                            type="text" 
                            id="nome" 
                            name="nome" 
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['nome']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
                            placeholder="João Silva"
                            value="<?= htmlspecialchars($_POST['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        >
                        <?php if (isset($erros['nome'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['nome'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- E-mail -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['email']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
                            placeholder="seu@email.com"
                            value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        >
                        <?php if (isset($erros['email'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['email'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Telefone -->
                    <div>
                        <label for="telefone" class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                        <input 
                            type="tel" 
                            id="telefone" 
                            name="telefone" 
                            placeholder="(11) 98765-4321"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            value="<?= htmlspecialchars($_POST['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>

                    <!-- Tipo de Usuário -->
                    <div>
                        <label for="tipo_usuario" class="block text-sm font-medium text-gray-700 mb-1">Tipo de conta</label>
                        <select 
                            id="tipo_usuario" 
                            name="tipo_usuario" 
                            required
                            onchange="atualizarFormulario()"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">-- Selecionar --</option>
                            <option value="TRABALHADOR" <?= $tipoUsuario === 'TRABALHADOR' ? 'selected' : '' ?>>Sou trabalhador/autônomo</option>
                            <option value="CONTRATANTE" <?= $tipoUsuario === 'CONTRATANTE' ? 'selected' : '' ?>>Sou contratante/empresa</option>
                        </select>
                        <?php if (isset($erros['tipo_usuario'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['tipo_usuario'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Documento (CPF/CNPJ) - Dinâmico conforme tipo -->
                    <div id="documentoDiv" class="<?= !$tipoUsuario || $tipoUsuario === '' ? 'hidden' : '' ?>">
                        <label id="documentoLabel" for="documento" class="block text-sm font-medium text-gray-700 mb-1">
                            <?= $tipoUsuario === 'CONTRATANTE' ? 'CNPJ' : 'CPF' ?>
                        </label>
                        <input 
                            type="text" 
                            id="documento" 
                            name="documento" 
                            placeholder="<?= $tipoUsuario === 'CONTRATANTE' ? '00.000.000/0000-00' : '000.000.000-00' ?>"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            value="<?= htmlspecialchars($_POST['documento'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        >
                        <?php if (isset($erros['documento'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['documento'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Senha -->
                    <div>
                        <label for="senha" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="senha" 
                                name="senha" 
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['senha']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
                                placeholder="••••••••"
                            >
                            <button 
                                type="button" 
                                onclick="document.getElementById('senha').type = document.getElementById('senha').type === 'password' ? 'text' : 'password'"
                                class="absolute right-3 top-2.5 text-gray-600 hover:text-gray-900"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        <?php if (isset($erros['senha'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['senha'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Confirmar Senha -->
                    <div>
                        <label for="confirma_senha" class="block text-sm font-medium text-gray-700 mb-1">Confirmar senha</label>
                        <input 
                            type="password" 
                            id="confirma_senha" 
                            name="confirma_senha" 
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['confirma_senha']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
                            placeholder="••••••••"
                        >
                        <?php if (isset($erros['confirma_senha'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['confirma_senha'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Botão Criar Conta -->
                    <button 
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200"
                    >
                        Criar conta
                    </button>
                </form>

                <!-- Link Login -->
                <p class="text-center text-gray-600 text-sm mt-6">
                    Já tem conta? 
                    <a href="/login" class="text-blue-600 hover:text-blue-700 font-semibold">Entrar</a>
                </p>
            </div>
        </div>
    </div>
</main>

<script>
function atualizarFormulario() {
    const tipoUsuario = document.getElementById('tipo_usuario').value;
    const documentoDiv = document.getElementById('documentoDiv');
    const documentoLabel = document.getElementById('documentoLabel');
    const documentoInput = document.getElementById('documento');
    
    if (tipoUsuario) {
        documentoDiv.classList.remove('hidden');
        if (tipoUsuario === 'CONTRATANTE') {
            documentoLabel.textContent = 'CNPJ';
            documentoInput.placeholder = '00.000.000/0000-00';
        } else {
            documentoLabel.textContent = 'CPF';
            documentoInput.placeholder = '000.000.000-00';
        }
    } else {
        documentoDiv.classList.add('hidden');
    }
}
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>
