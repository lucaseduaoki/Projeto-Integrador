<?php
$tituloPagina = 'Cadastro';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$erros = $erros ?? [];
$papeis = isset($_POST['papeis']) ? (array)$_POST['papeis'] : ['TRABALHADOR'];
$tipoPessoa = $_POST['tipo_pessoa'] ?? 'PF';
?>

<main class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-5xl grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Left Column - Ilustração -->
        <div class="hidden lg:flex flex-col justify-center bg-marca rounded-md p-12 text-white relative overflow-hidden">
            <span class="absolute -top-4 right-8 h-8 w-40 rounded-full bg-white/15"></span>
            <span class="absolute top-10 right-24 h-8 w-28 rounded-full bg-white/10"></span>
            <h2 class="text-3xl font-bold mb-8 leading-tight relative">Crie a conta e ache seu próximo serviço.</h2>
            <ul class="space-y-5 border-l border-white/40 pl-5">
                <li>
                    <p class="font-semibold">Trabalhador ou contratante</p>
                    <p class="text-blue-100 text-sm mt-0.5">Você define como vai usar a plataforma.</p>
                </li>
                <li>
                    <p class="font-semibold">Perfil simples</p>
                    <p class="text-blue-100 text-sm mt-0.5">Nome, contato e habilidades. O resto é opcional.</p>
                </li>
                <li>
                    <p class="font-semibold">Sem taxa de cadastro</p>
                    <p class="text-blue-100 text-sm mt-0.5">Entre, publique ou candidate-se.</p>
                </li>
            </ul>
        </div>

        <!-- Right Column - Formulário -->
        <div class="flex items-center">
            <div class="w-full bg-white rounded-md border border-gray-100 p-8 max-h-[90vh] overflow-y-auto">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Criar nova conta</h1>
                <p class="text-gray-600 mb-6">Cadastre-se em minutos e comece agora.</p>

                <!-- Erro Geral -->
                <?php if (isset($erros['geral'])): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm mb-6">
                        <?= htmlspecialchars($erros['geral'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= URL_BASE ?>/cadastro/submit" class="space-y-4">
                    

                    <!-- Nome Completo -->
                    <div>
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome completo</label>
                        <input 
                            type="text" 
                            id="nome" 
                            name="nome" 
                            required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['nome']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
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
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['email']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
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
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            value="<?= htmlspecialchars($_POST['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>

                    <!-- Tipo de pessoa -->
                    <fieldset>
                        <legend class="block text-sm font-medium text-gray-700 mb-1">Você é</legend>
                        <div class="flex gap-6">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="tipo_pessoa" value="PF" required onchange="atualizarDocumento()" <?= $tipoPessoa === 'PF' ? 'checked' : '' ?>>
                                Pessoa física
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="tipo_pessoa" value="PJ" onchange="atualizarDocumento()" <?= $tipoPessoa === 'PJ' ? 'checked' : '' ?>>
                                Empresa
                            </label>
                        </div>
                        <?php if (isset($erros['tipo_pessoa'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['tipo_pessoa'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </fieldset>

                    <!-- Papéis -->
                    <fieldset>
                        <legend class="block text-sm font-medium text-gray-700 mb-1">Como você vai usar a plataforma</legend>
                        <div class="space-y-1">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="papeis[]" value="TRABALHADOR" onchange="atualizarPapeis(this)" <?= in_array('TRABALHADOR', $papeis, true) ? 'checked' : '' ?>>
                                Sou trabalhador/prestador de serviço
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="papeis[]" value="CONTRATANTE" onchange="atualizarPapeis(this)" <?= in_array('CONTRATANTE', $papeis, true) ? 'checked' : '' ?>>
                                Sou contratante (publico vagas)
                            </label>
                        </div>
                        <p class="text-gray-500 text-xs mt-1">Pessoa física pode marcar os dois. Empresa atua em um único papel.</p>
                        <?php if (isset($erros['papeis'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['papeis'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </fieldset>

                    <!-- Documento (CPF/CNPJ) - Dinâmico conforme tipo -->
                    <div id="documentoDiv">
                        <label id="documentoLabel" for="documento" class="block text-sm font-medium text-gray-700 mb-1">
                            <?= $tipoPessoa === 'PJ' ? 'CNPJ' : 'CPF' ?>
                        </label>
                        <input 
                            type="text" 
                            id="documento" 
                            name="documento" 
                            placeholder="<?= $tipoPessoa === 'PJ' ? '00.000.000/0000-00' : '000.000.000-00' ?>"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            value="<?= htmlspecialchars($_POST['documento'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        >
                        <?php if (isset($erros['documento'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['documento'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Responsável (empresa prestadora de serviço) -->
                    <div id="responsavelDiv" class="<?= ($tipoPessoa === 'PJ' && in_array('TRABALHADOR', $papeis, true)) ? '' : 'hidden' ?>">
                        <label for="nome_responsavel" class="block text-sm font-medium text-gray-700 mb-1">Responsável pela execução do serviço</label>
                        <input
                            type="text"
                            id="nome_responsavel"
                            name="nome_responsavel"
                            maxlength="100"
                            placeholder="Nome da pessoa que executará o serviço"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            value="<?= htmlspecialchars($_POST['nome_responsavel'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        >
                        <?php if (isset($erros['nome_responsavel'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['nome_responsavel'], ENT_QUOTES, 'UTF-8') ?></p>
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
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['senha']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
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
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['confirma_senha']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
                            placeholder="••••••••"
                        >
                        <?php if (isset($erros['confirma_senha'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['confirma_senha'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Botão Criar Conta -->
                    <button 
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition-colors duration-200"
                    >
                        Criar conta
                    </button>
                </form>

                <!-- Link Login -->
                <p class="text-center text-gray-600 text-sm mt-6">
                    Já tem conta? 
                    <a href="<?= URL_BASE ?>/login" class="text-blue-600 hover:text-blue-700 font-semibold">Entrar</a>
                </p>
            </div>
        </div>
    </div>
</main>

<script>
// Responsável só aparece para empresa que presta serviço; a regra é validada no servidor.
function atualizarFormulario() {
    const trabalhador = document.querySelector('input[name="papeis[]"][value=TRABALHADOR]').checked;
    const pj = document.querySelector('input[name=tipo_pessoa]:checked').value === 'PJ';
    const mostrar = pj && trabalhador;
    document.getElementById('responsavelDiv').classList.toggle('hidden', !mostrar);
    document.getElementById('nome_responsavel').required = mostrar;
}

// Empresa atua em um único papel: marcar um desmarca o outro.
function atualizarPapeis(marcado) {
    const pj = document.querySelector('input[name=tipo_pessoa]:checked').value === 'PJ';
    if (pj && marcado.checked) {
        document.querySelectorAll('input[name="papeis[]"]').forEach(function (c) {
            if (c !== marcado) c.checked = false;
        });
    }
    atualizarFormulario();
}

function atualizarDocumento() {
    const pj = document.querySelector('input[name=tipo_pessoa]:checked').value === 'PJ';
    document.getElementById('documentoLabel').textContent = pj ? 'CNPJ' : 'CPF';
    document.getElementById('documento').placeholder = pj ? '00.000.000/0000-00' : '000.000.000-00';
    if (pj) {
        const marcados = document.querySelectorAll('input[name="papeis[]"]:checked');
        for (let i = 1; i < marcados.length; i++) marcados[i].checked = false;
    }
    atualizarFormulario();
}
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>
