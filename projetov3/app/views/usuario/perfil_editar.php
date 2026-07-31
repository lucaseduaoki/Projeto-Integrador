<?php
$tituloPagina = 'Editar Perfil';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$usuario = $usuario ?? null;
$habilidades = $habilidades ?? [];
$habilidadesUsuario = $habilidadesUsuario ?? [];
$erros = $erros ?? [];
$sucesso = $sucesso ?? null;

if (!$usuario) {
    header('Location: /login');
    exit;
}

$nomeUsuario = htmlspecialchars($usuario->getNome(), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($usuario->getEmail(), ENT_QUOTES, 'UTF-8');
$telefone = htmlspecialchars($usuario->getTelefone() ?? '', ENT_QUOTES, 'UTF-8');
$descricao = htmlspecialchars($usuario->getDescricao() ?? '', ENT_QUOTES, 'UTF-8');
$tipoUsuario = htmlspecialchars($usuario->getTipoUsuario(), ENT_QUOTES, 'UTF-8');
?>

<main class="flex-1">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Editar Perfil</h1>

            <!-- Mensagem de Sucesso -->
            <?php if ($sucesso): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm mb-6">
                    <?= htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <!-- Erro Geral -->
            <?php if (isset($erros['geral'])): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">
                    <?= htmlspecialchars($erros['geral'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/perfil/editar" enctype="multipart/form-data" class="space-y-6">
                

                <!-- Foto de Perfil -->
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Foto de Perfil</h3>
                    <div class="flex flex-col sm:flex-row gap-6 items-start">
                        <div class="w-24 h-24 rounded-full bg-blue-600 text-white flex items-center justify-center text-3xl font-bold flex-shrink-0">
                            <?= htmlspecialchars(strtoupper(substr($usuario->getNome(), 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="flex-1">
                            <input 
                                type="file" 
                                id="foto_perfil" 
                                name="foto_perfil" 
                                accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors"
                            >
                            <p class="text-xs text-gray-600 mt-2">Formatos suportados: JPG, PNG, GIF (máx 2MB)</p>
                        </div>
                    </div>
                </div>

                <!-- Informações Pessoais -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Pessoais</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                            <input 
                                type="text" 
                                id="nome" 
                                name="nome" 
                                required
                                value="<?= $nomeUsuario ?>"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['nome']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
                            >
                            <?php if (isset($erros['nome'])): ?>
                                <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['nome'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="telefone" class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                            <input 
                                type="tel" 
                                id="telefone" 
                                name="telefone" 
                                value="<?= $telefone ?>"
                                placeholder="(11) 98765-4321"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                        </div>

                        <div class="sm:col-span-2">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="<?= $email ?>"
                                disabled
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 text-gray-600"
                            >
                            <p class="text-xs text-gray-500 mt-1">E-mail não pode ser alterado</p>
                        </div>
                    </div>
                </div>

                <!-- Descrição/Bio -->
                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Sobre você</label>
                    <textarea 
                        id="descricao" 
                        name="descricao" 
                        rows="4"
                        placeholder="Fale um pouco sobre você, suas experiências e serviços que oferece..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['descricao']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
                    ><?= $descricao ?></textarea>
                    <?php if (isset($erros['descricao'])): ?>
                        <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['descricao'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Localização -->
                <div>
                    <label for="localizacao" class="block text-sm font-medium text-gray-700 mb-1">Localização</label>
                    <input 
                        type="text" 
                        id="localizacao" 
                        name="localizacao" 
                        placeholder="Cidade, Estado"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>

                <!-- Habilidades (apenas trabalhadores) -->
                <?php if ($usuario->isTrabalhador()): ?>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Habilidades</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <?php foreach ($habilidades as $habilidade): 
                                $idHabilidade = $habilidade->getId();
                                $checked = in_array($idHabilidade, $habilidadesUsuario);
                            ?>
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        name="habilidades[]" 
                                        value="<?= $idHabilidade ?>"
                                        <?= $checked ? 'checked' : '' ?>
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer"
                                    >
                                    <span class="ml-2 text-sm text-gray-700 cursor-pointer">
                                        <?= htmlspecialchars($habilidade->getNome(), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Botões -->
                <div class="flex gap-4 pt-6 border-t border-gray-200">
                    <button 
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-200"
                    >
                        Salvar alterações
                    </button>
                    <a 
                        href="/perfil"
                        class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-6 rounded-lg transition-colors duration-200"
                    >
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
