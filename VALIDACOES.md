# FreelaJá: validações de cada formulário

Este documento lista, formulário por formulário, o que é conferido antes de um dado ser aceito pelo sistema. Tudo foi levantado direto do código da versão final (conferido em 2026-11-20). As referências entre parênteses apontam o arquivo e o método onde a validação mora.

---

## Como a validação funciona

Uma mesma entrada pode passar por até quatro camadas:

| Camada | Onde | O que faz | Pode ser burlada? |
|---|---|---|---|
| **Navegador** | atributos HTML (`required`, `min`, `maxlength`, `type="email"`…) e JavaScript nas views | Avisa o usuário antes de enviar | Sim. Basta desativar o JavaScript ou enviar o POST direto. Serve só para conforto. |
| **Controller** | `app/helpers/Validador.php`, chamado nos controllers | Valida formato e preenchimento de cada campo | Não |
| **Service** | `app/services/*Service.php` | Aplica as regras de negócio (dono da vaga, limite de aceitos, prazo, duplicidade…) | Não |
| **Banco** | `app/database/scripts/script.sql` | Tamanho das colunas, `NOT NULL`, `UNIQUE`, `ENUM`, `CHECK` e chaves estrangeiras | Não. É a última barreira. |

**Como o erro chega ao usuário:**
- Erro de campo (do `Validador`) aparece em vermelho embaixo do próprio campo, e o formulário volta preenchido.
- Erro de regra de negócio (exceção lançada no service) aparece como mensagem geral no topo do formulário ou como mensagem *flash* depois de um redirecionamento.
- Erro de banco (`PDOException`) nunca mostra o detalhe técnico: `Controller::mensagemAmigavel()` grava o erro no log e mostra só uma frase genérica ("Não foi possível… Tente novamente em instantes.").

### Métodos do `Validador`

O `Validador` é encadeável (`$v->obrigatorio(...)->email(...)`), guarda **um erro por campo** e quase todos os métodos ignoram valor vazio (quem obriga o preenchimento é o `obrigatorio()`).

| Método | Regra |
|---|---|
| `obrigatorio` | Não pode ser vazio (o texto `"0"` é aceito) |
| `minimo` / `maximo` | Quantidade mínima/máxima de caracteres, contados com `mb_strlen` (acento conta como 1) |
| `tamanhoMinMax` / `tamanhoMax` | Mesma ideia, com faixa ou só máximo |
| `email` | Formato de e-mail (`FILTER_VALIDATE_EMAIL`) |
| `telefone` | 10 ou 11 dígitos depois de remover tudo que não é número (DDD + número) |
| `cpf` / `cnpj` | 11 / 14 dígitos, recusa sequências repetidas (`111.111.111-11`) e **calcula os dígitos verificadores** |
| `documentoPorTipoPessoa` | Escolhe `cpf()` para pessoa física e `cnpj()` para pessoa jurídica |
| `papeis` | Pessoa física só pode ser trabalhador; pessoa jurídica só pode ser contratante |
| `emLista` | O valor precisa estar numa lista fechada |
| `dataValida` | Data real no formato `aaaa-mm-dd` (recusa `2026-02-30`) |
| `dataNaoAnterior` | Data **a partir de amanhã**. Aceita uma data "permitida" extra (usado na edição de vaga) |
| `horaValida` | Hora `hh:mm` entre `00:00` e `23:59` |
| `monetario` | Até 8 dígitos e até 2 casas decimais, maior que zero. Recusa `1e3`, sinais e separador de milhar |
| `numerico` | `is_numeric` |
| `erro` | Registra um erro decidido pelo próprio controller |

---

## 1. Login — `/login`

`AutenticacaoController::logar()` → `AutenticacaoService::logar()`

| Campo | Navegador | Servidor |
|---|---|---|
| E-mail | `required`, `type="email"` | Obrigatório; formato de e-mail válido |
| Senha | `required` | Obrigatória (sem tamanho mínimo no login) |

**Regras de negócio:**
- O e-mail precisa pertencer a um usuário **ativo**. Se não existir ou estiver bloqueado: *"Usuário não encontrado ou inativo."*
- A senha é conferida com `password_verify()` contra o hash bcrypt. Se não bater: *"Credenciais inválidas."*
- Quem já está logado e abre `/login` é mandado direto para `/vagas`.

---

## 2. Cadastro — `/cadastro`

`AutenticacaoController::cadastrar()` → `UsuarioService::registrar()`

| Campo | Navegador | Servidor |
|---|---|---|
| Nome | `required` | Obrigatório; mínimo de 3 caracteres |
| E-mail | `required`, `type="email"` | Obrigatório; formato válido; **não pode já estar cadastrado** (*"Este e-mail já está cadastrado."*), e o banco também tem `UNIQUE` |
| Telefone | `required` | Obrigatório; 10 ou 11 dígitos (*"Telefone inválido. Informe DDD e número…"*) |
| Tipo de pessoa (PF/PJ) | `required` | Obrigatório; só `PF` ou `PJ` |
| CPF / CNPJ | `required`, `maxlength="18"`, máscara automática por JavaScript | Obrigatório; CPF válido para PF ou CNPJ válido para PJ, com dígito verificador. Gravado só com os dígitos |
| Senha | `required` | Obrigatória; mínimo de 8 caracteres |
| Confirmar senha | `required`; JavaScript bloqueia o envio se for diferente da senha | Obrigatória (o servidor **não** compara com a senha, ver "Lacunas") |

**Regras de negócio:**
- O papel não é escolhido pelo usuário: o controller define **PF = trabalhador** e **PJ = contratante**, e `Validador::papeis()` confere essa combinação. No banco, `chk_usuario_tem_papel` exige pelo menos um papel e `chk_usuario_pj_papel_unico` impede PJ com dois papéis.
- A senha é gravada com `password_hash()` (bcrypt, custo 12), nunca em texto puro.
- Depois do cadastro o usuário já entra logado e é levado para `/perfil`.
- Quem já está logado e abre `/cadastro` é mandado para `/vagas`.

---

## 3. Editar perfil — `/perfil/editar`

`UsuarioController::editarPerfil()` → `UsuarioService`

| Campo | Navegador | Servidor |
|---|---|---|
| Nome | `required` | Obrigatório; máximo de 100 caracteres |
| Telefone | — | Máximo de 20 caracteres (sem checagem de formato, ver "Lacunas") |
| E-mail | campo `disabled` | Não pode ser alterado: o valor nem é lido do formulário |
| CPF / CNPJ | `maxlength="18"`, máscara por JavaScript | Obrigatório; CPF ou CNPJ válido de acordo com o tipo de pessoa **já cadastrado** (o tipo não muda pelo perfil); máximo de 20 dígitos |
| Sobre você | — | Máximo de 500 caracteres |
| Foto de perfil | `accept="image/jpeg,image/png,image/gif"` | Ver abaixo |
| Habilidades (só trabalhador) | caixas de seleção | Ver abaixo |

**Foto de perfil** (`app/helpers/UploadImagemValidador.php`), opcional:
1. O upload precisa ter terminado sem erro (*"Não foi possível enviar a imagem."*).
2. O arquivo precisa ter vindo mesmo do upload do PHP (`is_uploaded_file`), e não de um caminho forjado.
3. O tipo é descoberto pelo **conteúdo** do arquivo (`finfo`), não pela extensão: só JPG, PNG ou GIF.
4. `getimagesize()` precisa reconhecer o arquivo como imagem.
5. O arquivo não pode conter o texto `<?php` (barra imagem com código PHP escondido).
6. O arquivo é salvo com nome aleatório (`random_bytes`) e a extensão do tipo detectado. A foto antiga só é apagada depois que a nova foi gravada, e, se algo falhar, a nova é descartada.

**Habilidades** (`UsuarioService::definirHabilidades()`):
- Só trabalhador tem habilidades.
- Cada id precisa ser um número inteiro positivo (`"1;DROP"` é recusado, não convertido) e existir na tabela `habilidade` (*"Habilidade inválida."*).
- A troca é feita numa transação: ou grava todas, ou nenhuma.

---

## 4. Criar e editar vaga — `/vagas/criar` e `/vagas/editar`

`VagaController::criar()` / `editar()` → `validarDadosVaga()` → `VagaService`

Só contratante logado acessa. Criação e edição usam as mesmas regras de campo.

| Campo | Navegador | Servidor |
|---|---|---|
| Categoria | `required` (lista) | Obrigatória |
| Título (função) | `required`; `readonly` se a vaga já tiver candidaturas | Obrigatório; entre 2 e 100 caracteres |
| Descrição | `required` | Obrigatória; entre 10 e 1000 caracteres |
| Duração | `maxlength="50"` | Obrigatória; máximo de 50 caracteres |
| Localização (endereço) | `required`, `maxlength="150"` | Sem validação no servidor (ver "Lacunas"); coluna de 150 caracteres no banco |
| Bairro | `required`, `maxlength="100"` | Obrigatório; entre 2 e 100 caracteres |
| Remuneração | `required`, `type="number"`, `min="0"`, `step="0.01"` | Obrigatória; valor monetário válido e maior que zero. Vírgula é convertida em ponto antes da validação |
| Data do serviço | `required`, `type="date"`, `min` = amanhã | Obrigatória; data válida; **a partir de amanhã** (*"…pois o prazo de candidatura encerra um dia antes."*) |
| Horário | `required`, `type="time"` | Obrigatório; formato `hh:mm` |
| Qtd. de trabalhadores | `type="number"`, `min="1"` | Na criação, só convertido para inteiro (ver "Lacunas"). Na edição, não pode ficar abaixo do número de aceitos |
| Observações | `maxlength="500"` | Máximo de 500 caracteres |

**Regras de negócio:**
- **Criar** (`VagaService::validarCriadorVaga()`): só contratante com conta ativa.
- **Prazo de candidatura**: não é digitado. O controller grava `data_limite = data do serviço − 1 dia`.
- **Editar**:
  - só o dono da vaga pode editar (`contratanteRequired()` + `podeGerenciarVaga()`), senão vai para `/403`;
  - anúncio removido pela moderação não pode ser editado;
  - se a vaga já tem candidaturas, **título e categoria ficam travados** (`VagaService::validarEdicaoVaga()`, conferido de novo em `atualizar()`);
  - o limite de trabalhadores não pode ser reduzido abaixo da quantidade já aceita (`VagaService::validarNovoLimite()`);
  - a data do serviço já gravada continua aceita mesmo que tenha passado, para o dono conseguir corrigir outros campos.

---

## 5. Busca de vagas — `/vagas/buscar`

`VagaController::buscar()` → `VagaRepository::buscar()`

Na busca, um filtro inválido **não bloqueia** a pesquisa: ele é ignorado e um aviso aparece na tela.

| Filtro | Navegador | Servidor |
|---|---|---|
| Palavra-chave | — | Livre (vai como parâmetro do SQL, nunca concatenado) |
| Categoria | lista | Livre (parâmetro do SQL) |
| Remuneração mínima | `type="number"`, `min="0"` | Precisa ser número e não negativo; senão é ignorada (*"…deve ser um número e foi ignorada."*) |
| Data a partir de | `type="date"` | Data válida; senão é ignorada (*"A data do filtro é inválida e foi ignorada."*) |
| Ordenar | lista | Só `recentes`, `maior_salario` ou `menor_salario`; qualquer outro valor vira `recentes`. O texto nunca entra no `ORDER BY`, que é montado por um `match` |

---

## 6. Candidatar-se e cancelar candidatura — botão na página da vaga

### Candidatar-se — `POST /candidatura/demonstrar`

`CandidaturaController::demonstrar()` → `CandidaturaService::validarCandidatura()` e `demonstrarInteresse()`

O formulário só envia o `id_vaga`. O servidor confere:
- usuário logado e **trabalhador**;
- a vaga existe, está `ATIVA`, visível e o dono está com a conta ativa;
- o prazo de candidatura (`data_limite`) não passou;
- ainda há vaga disponível (aceitos < limite);
- o trabalhador não é o dono da vaga;
- o trabalhador **ainda não se candidatou** a essa vaga.

### Cancelar candidatura — `POST /candidatura/remover`

`CandidaturaController::remover()` → `CandidaturaService::removerInteresse()`
- Navegador: pede confirmação (*"Deseja remover sua candidatura desta vaga?"*).
- O trabalhador precisa ter candidatura nessa vaga.
- Só cancela se ela ainda estiver **pendente**. Se já foi aceita: *"Sua candidatura já foi aceita. Entre em contato com o contratante para cancelar."*

---

## 7. Ações do contratante sobre a vaga

### Aceitar candidato — `POST /candidatura/aceitar`

`CandidaturaService::validarAceitacaoCandidato()` e `aceitarInteressado()`
- Só contratante logado, e só na **própria** vaga (*"Sem permissão."*).
- A candidatura e a vaga precisam existir.
- A vaga precisa estar ativa e visível.
- O candidato não pode já estar aceito.
- Não pode passar do limite de trabalhadores da vaga.

### Encerrar vaga — `POST /vagas/encerrar`

`VagaService::validarEncerramentoVaga()`
- Só o dono da vaga.
- Só encerra quando o número de aceitos **atingiu o limite** (*"A vaga não pode ser encerrada. Você precisa de N trabalhador(es) aceito(s)…"*).

### Excluir vaga — `POST /vagas/excluir`

`VagaService::deletar()`
- Navegador: pede confirmação (*"Tem certeza? Esta ação não pode ser desfeita."*).
- Só o dono da vaga.
- Anúncio oculto ou removido pela moderação não pode ser excluído (fica como evidência da denúncia).
- A exclusão é lógica: grava `excluida_em` e o registro continua no banco.

---

## 8. Denunciar usuário ou anúncio — `/denuncia/criar`

`DenunciaController::exibirFormDenunciar()` / `denunciar()` → `DenunciaService::criar()`

**Antes de abrir o formulário:**
- usuário logado;
- o alvo (anúncio ou usuário) precisa existir;
- ninguém denuncia o próprio anúncio nem a si mesmo.

| Campo | Navegador | Servidor |
|---|---|---|
| Alvo (`id_vaga` ou `id_usuario_denunciado`, ocultos) | — | Conferido de novo no envio: precisa existir e não pode ser o próprio usuário. O dono do anúncio é buscado no banco, não vem do formulário |
| Motivo | `required` (lista) | Obrigatório; precisa estar na lista de motivos **do tipo de alvo** (anúncio e usuário têm listas diferentes) |
| Descrição | — | Obrigatória; máximo de 1000 caracteres |

O service ainda garante que a denúncia tem **exatamente um** alvo: anúncio ou usuário, nunca os dois e nunca nenhum.

---

## 9. Registrar não comparecimento — `/denuncia/nao-comparecimento`

`DenunciaController::registrarNaoComparecimento()` → `DenunciaService::validarNaoComparecimento()`

| Campo | Navegador | Servidor |
|---|---|---|
| Candidatura (`id_interesse`, oculto) | — | Ver regras abaixo |
| Descrição | `maxlength="1000"` | Obrigatória; máximo de 1000 caracteres |

**Regras de negócio** (conferidas ao abrir o formulário e de novo ao enviar):
- só contratante logado;
- a candidatura e a vaga precisam existir;
- a vaga precisa ser **do próprio contratante**;
- o trabalhador precisa ter sido **aceito**;
- não pode já existir um registro de não comparecimento desse trabalhador nessa vaga.

---

## 10. Moderação de denúncias (admin) — `/admin/denuncias`

`DenunciaController::moderar()` → `DenunciaService`

- Só administrador.
- O id da denúncia precisa ser maior que zero e a ação precisa ser `bloquear`, `remover` ou `arquivar`; qualquer outra coisa volta para a lista sem fazer nada.
- A denúncia precisa existir e ainda estar **pendente** (*"Esta denúncia já foi analisada."*).
- **Remover anúncio** só vale para denúncia de anúncio (*"Esta denúncia não é de um anúncio."*).
- **Bloquear** precisa de um alvo que ainda exista (*"O alvo da denúncia não existe mais."*).
- Remover e bloquear pedem confirmação no navegador antes de enviar.

---

## Lacunas encontradas

Pontos em que a validação está incompleta ou só existe no navegador. Nada disso impede o uso normal do sistema, mas vale saber caso a banca pergunte.

1. **Máscara de CPF/CNPJ e conferência de senha não estão funcionando no navegador.** Em `app/views/autenticacao/cadastro.php` e `app/views/usuario/perfil_editar.php`, o bloco `<script>` do fim do arquivo não tem o `</script>` de fechamento (a tag sumiu na versão do zip). Com isso o navegador lê o rodapé como se fosse JavaScript, dá erro, e o script inteiro não roda. A validação do servidor continua funcionando.
2. **Confirmação de senha só no navegador.** O servidor exige que "Confirmar senha" seja preenchido, mas não compara com a senha. Somado ao item 1, hoje é possível cadastrar com as duas senhas diferentes (vale a primeira).
3. **Foto de perfil sem limite de tamanho.** Não há limite de MB nem de dimensões na aplicação; vale só o `upload_max_filesize` do `php.ini`.
4. **Quantidade de trabalhadores sem validação no servidor na criação.** Só o navegador exige no mínimo 1; enviando o POST direto, dá para criar vaga com 0 ou número negativo.
5. **Localização da vaga** é obrigatória só no navegador; no servidor não é checada.
6. **Telefone no perfil** não é obrigatório nem tem o formato conferido na edição (no cadastro é).
7. **CPF/CNPJ não é único.** Nem a aplicação nem o banco impedem duas contas com o mesmo documento.
8. **Nome no cadastro sem tamanho máximo na aplicação.** Um nome com mais de 100 caracteres esbarra no limite da coluna do banco e o usuário vê só a mensagem genérica de erro.
9. **Categoria da vaga** não é conferida contra a tabela `categoria`; um id inexistente esbarra na chave estrangeira e também cai na mensagem genérica.
10. **Mensagens diferentes no login** ("Usuário não encontrado ou inativo" × "Credenciais inválidas") permitem descobrir se um e-mail está cadastrado.
11. **Sem token CSRF.** Nenhum formulário `POST` tem token anti-CSRF; a proteção depende só da sessão do usuário.
