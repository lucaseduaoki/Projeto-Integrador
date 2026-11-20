<?php
$tituloPagina = 'Meus Anúncios';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$vagas = $vagas ?? [];
?>

<main class="flex-1">
<div class="max-w-7xl mx-auto px-4 py-8">

<div class="flex items-center justify-between mb-8">

<h1 class="text-3xl font-bold">
Meus Anúncios
</h1>

<a
href="<?= URL_BASE ?>/vagas/criar"
class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded">

Novo anúncio

</a>

</div>

<?php if(empty($vagas)): ?>

<div class="bg-white rounded-md border p-10 text-center">

<p class="text-gray-500 text-lg">
Você ainda não publicou nenhum anúncio.
</p>

<a
href="<?= URL_BASE ?>/vagas/criar"
class="inline-block mt-6 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded">

Criar anúncio

</a>

</div>

<?php else: ?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">

<?php foreach($vagas as $vaga): ?>

<?php

$aceitos = $vaga->getTotalAceitos();
$limite = $vaga->getTrabalhadoresLimite();

$porcentagem = $limite > 0
? min(100, ($aceitos / $limite) * 100)
: 0;

$status = $vaga->getStatus();

$titulo = htmlspecialchars($vaga->getTitulo(), ENT_QUOTES, 'UTF-8');

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

$statusBadge = $status === 'ATIVA'
? 'bg-green-100 text-green-800'
: 'bg-gray-100 text-gray-700';

?>

<div class="bg-white rounded-md border border-gray-100 p-6 h-full flex flex-col hover:border-blue-500 transition-colors">

<div class="flex-1">

<div class="flex justify-between items-center mb-3">

<?php if(!$vaga->estaVisivel()): ?>

<span class="px-2 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
<?= $vaga->foiRemovidaPelaModeracao() ? 'Removida pela moderação' : 'Oculta pela moderação' ?>
</span>

<?php else: ?>

<span class="px-2 py-1 rounded-full text-xs font-semibold <?= $statusBadge ?>">
<?= $status === 'ATIVA' ? 'Ativa' : 'Encerrada' ?>
</span>

<?php endif; ?>

<?php if ($vaga->getCategoriaNome()): ?>
<span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
<?= htmlspecialchars($vaga->getCategoriaNome(), ENT_QUOTES, 'UTF-8') ?>
</span>
<?php endif; ?>

</div>

<h3 class="text-lg font-semibold text-gray-900 mb-2">
<?= $titulo ?>
</h3>

<p class="text-sm text-gray-600 mb-5">
<?= $descricao ?>
</p>

<div class="space-y-2 text-sm text-gray-600">

<div class="flex items-center gap-2">

<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
</svg>

<?= $localizacao ?>

</div>

<div class="flex items-center gap-2">

<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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

</div>

<div class="mt-5 pt-1">

<div class="flex justify-between text-sm text-gray-600 mb-2">
<span>Trabalhadores aceitos</span>
<span><?= $aceitos ?> / <?= $limite ?></span>
</div>

<div class="bg-gray-200 rounded-full h-2">
<div class="bg-green-600 h-2 rounded-full" style="width:<?= $porcentagem ?>%"></div>
</div>

</div>

<div class="pt-4 mt-4 border-t border-gray-100">

<div class="grid grid-cols-2 gap-2">

<a
href="<?= URL_BASE ?>/vagas/visualizar?id=<?= $vaga->getIdVaga() ?>"
class="text-center bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold py-2 rounded-md transition-colors">

Visualizar

</a>

<a
href="<?= URL_BASE ?>/vagas/editar?id=<?= $vaga->getIdVaga() ?>"
class="text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 rounded-md transition-colors">

Editar

</a>

</div>

<a
href="<?= URL_BASE ?>/candidatura/interessados?id=<?= $vaga->getIdVaga() ?>"
class="block mt-3 text-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded-md transition-colors">

Ver interessados

</a>

<button
onclick="abrirContatos(<?= $vaga->getIdVaga() ?>)"
class="w-full mt-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-md transition-colors">

Ver contatos aprovados

</button>

<?php if($status === 'ENCERRADA'): ?>
<button
disabled
class="w-full mt-3 bg-gray-200 text-gray-500 font-semibold py-2 rounded-md cursor-not-allowed">
Vaga encerrada
</button>
<?php else: ?>
<form
method="POST"
action="<?= URL_BASE ?>/vagas/encerrar"
class="mt-3"
onsubmit="<?php if($aceitos < $limite): ?>alert('A vaga só pode ser encerrada quando o número de aceitos atingir o limite.'); return false;<?php endif; ?>">

<input
type="hidden"
name="id"
value="<?= $vaga->getIdVaga() ?>">

<button
type="submit"
class="w-full <?php if($aceitos < $limite): ?>bg-gray-200 text-gray-500 cursor-not-allowed<?php else: ?>bg-black text-white hover:bg-gray-800<?php endif; ?> font-semibold py-2 rounded-md transition-colors">

Encerrar vaga

</button>

</form>
<?php endif; ?>

<form
method="POST"
action="<?= URL_BASE ?>/vagas/excluir"
class="mt-3">

<input
type="hidden"
name="id"
value="<?= $vaga->getIdVaga() ?>">

<button
class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2 rounded-md transition-colors">

Excluir

</button>

</form>

</div>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>
</main>


<!-- Modal -->

<div
id="modalContatos"
class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">

<div class="bg-white rounded-md w-full max-w-3xl p-6">

<div class="flex justify-between items-center mb-5">

<h2 class="text-2xl font-bold">

Contatos dos trabalhadores aprovados

</h2>

<button
onclick="fecharContatos()"
class="text-2xl">

&times;

</button>

</div>

<table class="w-full border">

<thead class="bg-gray-100">

<tr>

<th class="border p-2 text-left">
Nome
</th>

<th class="border p-2 text-left">
Email
</th>

<th class="border p-2 text-left">
Telefone
</th>

</tr>

</thead>

<tbody id="tbodyContatos">

<tr>

<td colspan="3"
class="text-center p-4">

Carregando...

</td>

</tr>

</tbody>

</table>

</div>

</div>

<script>

function abrirContatos(idVaga){

document.getElementById('modalContatos').classList.remove('hidden');

const tbody = document.getElementById('tbodyContatos');

tbody.innerHTML =
'<tr><td colspan="3" class="text-center p-4">Carregando...</td></tr>';

fetch('<?= URL_BASE ?>/candidatura/aceitos?id=' + idVaga)

.then(response => response.json())

.then(dados => {

tbody.innerHTML = '';

if(dados.length === 0){

tbody.innerHTML = `
<tr>
<td colspan="3" class="text-center p-4">
Nenhum trabalhador aprovado.
</td>
</tr>
`;

return;

}

dados.forEach(trabalhador => {

tbody.innerHTML += `
<tr>

<td class="border p-2">${trabalhador.nome}</td>

<td class="border p-2">${trabalhador.email}</td>

<td class="border p-2">${trabalhador.telefone}</td>

</tr>
`;

});

})

.catch(() => {

tbody.innerHTML = `
<tr>
<td colspan="3" class="text-center text-red-600 p-4">
Erro ao carregar contatos.
</td>
</tr>
`;

});

}

function fecharContatos(){

document.getElementById('modalContatos').classList.add('hidden');

}

</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>