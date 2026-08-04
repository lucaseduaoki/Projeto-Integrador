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
href="/vagas/criar"
class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">

Novo anúncio

</a>

</div>

<?php if(empty($vagas)): ?>

<div class="bg-white rounded-xl border p-10 text-center">

<p class="text-gray-500 text-lg">
Você ainda não publicou nenhum anúncio.
</p>

<a
href="/vagas/criar"
class="inline-block mt-6 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">

Criar anúncio

</a>

</div>

<?php else: ?>

<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">

<?php foreach($vagas as $vaga): ?>

<?php

$aceitos = $vaga->getTotalAceitos();
$limite = $vaga->getTrabalhadoresLimite();

$porcentagem = $limite > 0
? min(100, ($aceitos / $limite) * 100)
: 0;

$status = $vaga->getStatus();

?>

<div class="bg-white border rounded-xl p-6 shadow-sm">

<div class="flex justify-between">

<h2 class="font-bold text-xl">
<?= htmlspecialchars($vaga->getTitulo()) ?>
</h2>

<?php if($status === 'ATIVA'): ?>

<span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">
ATIVA
</span>

<?php else: ?>

<span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs">
ENCERRADA
</span>

<?php endif; ?>

</div>

<p class="mt-3 text-gray-600">

<?= htmlspecialchars($vaga->getDescricao()) ?>

</p>

<div class="mt-5 text-lg font-bold text-green-700">

R$ <?= number_format($vaga->getRemuneracao(),2,',','.') ?>

</div>

<div class="mt-5">

<div class="flex justify-between text-sm mb-2">

<span>Trabalhadores aceitos</span>

<span>

<?= $aceitos ?> / <?= $limite ?>

</span>

</div>

<div class="bg-gray-200 rounded-full h-3">

<div
class="bg-green-600 h-3 rounded-full"
style="width:<?= $porcentagem ?>%">
</div>

</div>

</div>

<div class="grid grid-cols-2 gap-2 mt-6">

<a
href="/vagas/visualizar?id=<?= $vaga->getIdVaga() ?>"
class="text-center bg-blue-50 py-2 rounded">

Visualizar

</a>

<a
href="/vagas/editar?id=<?= $vaga->getIdVaga() ?>"
class="text-center bg-gray-100 py-2 rounded">

Editar

</a>

</div>

<a
href="/interesse/interessados?id=<?= $vaga->getIdVaga() ?>"
class="block mt-3 text-center bg-green-600 text-white py-2 rounded">

Ver interessados

</a>

<button
onclick="abrirContatos(<?= $vaga->getIdVaga() ?>)"
class="w-full mt-3 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">

Ver contatos aprovados

</button>

<form
method="POST"
action="/vagas/excluir"
class="mt-3">

<input
type="hidden"
name="id"
value="<?= $vaga->getIdVaga() ?>">

<button
class="w-full bg-red-50 text-red-600 py-2 rounded">

Excluir

</button>

</form>

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

<div class="bg-white rounded-xl w-full max-w-3xl p-6">

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

fetch('/interesse/aceitos?id=' + idVaga)

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