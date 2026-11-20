# FreelaJá: guia para entender e defender o código

Este documento existe para que você consiga explicar o sistema de cabeça, sem decorar arquivo por arquivo. Tudo o que está aqui foi conferido direto no código do repositório e testado rodando a aplicação de verdade; onde algo não pôde ser confirmado, está escrito "não confirmado". As referências entre parênteses (`arquivo:linha` ou só `arquivo`) servem para você abrir e conferir.

Uma advertência de método: os números de linha mudam quando alguém edita o arquivo. Se uma linha citada não bater, procure o nome do método ao lado dela.

---

## 1. Visão geral em uma página

**O que é.** O FreelaJá é uma plataforma web que conecta quem precisa de um serviço pontual (o **contratante**) a quem presta esse serviço (o **trabalhador**). O contratante publica uma **vaga** (anúncio), trabalhadores se **candidatam**, o contratante **aceita** quem quiser (respeitando um limite de vagas) e só então os dados de contato ficam visíveis entre as partes. Um **administrador** modera a plataforma por meio de **denúncias**. O sistema não tem pagamento: ele apenas aproxima as partes.

**Regra de papéis (importante, foi reforçada recentemente).** O cadastro não deixa mais o usuário escolher livremente: **pessoa física (CPF) é sempre trabalhador**, **pessoa jurídica (CNPJ) é sempre contratante**. Essa é uma regra de negócio explícita — não existe mais acúmulo de papéis nem conversão de um para o outro depois do cadastro.

**Decisão arquitetural central.** É um monólito em **PHP puro** com **MVC próprio**, sem framework, sem Composer e sem ORM. O roteador, o carregador de classes e o controller base foram escritos pela equipe (`app/core/`). A regra que organiza tudo é a divisão em camadas com uma responsabilidade cada: o **controller** recebe a requisição, autentica/autoriza e valida a entrada; o **service** decide as regras de negócio (RN); o **repository** fala SQL; o **model** representa os dados; e a **view** só desenha HTML. O banco é **MySQL** acessado por **PDO** com consultas parametrizadas.

**Tamanho (conferido em 2026-11-20, versão final).** 53 arquivos PHP, cerca de 9.200 linhas. 34 rotas, 7 tabelas, 2 triggers. 6 controllers, 5 services, 4 repositories, 5 models, 3 helpers, 24 views. Não há testes automatizados.

**Se a banca pedir "descreva seu sistema", uma resposta de cabeça:** *"É uma plataforma de vagas de serviços pontuais em PHP com MVC próprio. Toda requisição entra por um único arquivo, `public/index.php`, que registra as rotas; o roteador escolhe um controller; o controller confere quem é o usuário e valida a entrada; o service aplica as regras de negócio, como limite de trabalhadores aceitos, candidatura duplicada e liberação de contato só depois da seleção; o repository executa SQL parametrizado no MySQL; e uma view renderiza o resultado. Pessoa física só pode ser trabalhador, pessoa jurídica só pode ser contratante — sem acúmulo. A moderação é feita por denúncias: bloqueio de conta, remoção lógica de anúncio ou arquivamento sem sanção, sem apagar dados."*

---

## 2. O caminho de uma requisição

O melhor jeito de entender a estrutura é seguir uma requisição real do começo ao fim. Vamos acompanhar este caso: **um trabalhador clica em "Candidatar-se" na página de uma vaga**.

### 2.1 Da porta de entrada ao roteador

1. O navegador envia `POST /candidatura/demonstrar` com o campo `id_vaga`. O formulário está em `app/views/vaga/vaga_show.php`.
2. **`public/.htaccess`** reescreve qualquer caminho que não seja um arquivo/diretório real para `public/index.php`. Por isso existe um único ponto de entrada.
3. **`public/index.php`** faz, nesta ordem: carrega o autoload (`app/core/Autoload.php`), carrega a configuração (`app/config/Config.php`), registra um tratador global de exceções (`set_exception_handler`), cria o `Router`, declara as 34 rotas e chama `$router->run()`.
   - O **autoload** converte um nome de classe em caminho de arquivo: `app\services\VagaService` vira `app/services/VagaService.php`. É por isso que os namespaces espelham as pastas.
   - O **`Config.php`** define as constantes de banco **diretamente no código** (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` — não há `.env`, foi removido de propósito; na versão final do repositório esses valores vêm **vazios** e precisam ser preenchidos em cada máquina), configura a sessão (cookie `httponly`, `secure` só se a requisição for HTTPS, `use_strict_mode`), define o fuso `America/Sao_Paulo`, inicia a sessão e calcula `URL_BASE` a partir do host da requisição.
4. **`app/core/Router.php`.** O método `run()` normaliza a URI, procura uma rota cujo caminho **e** método HTTP batam exatamente, e chama `dispatch()`. O roteador só faz correspondência exata: **não existe parâmetro na URL**. Por isso os identificadores viajam sempre na query string (`?id=3`) ou no corpo do POST (`id_vaga`). Se nada casar, renderiza `errors/404.php`. Ao casar, `dispatch()` faz `new CandidaturaController` e chama o método `demonstrar`.

### 2.2 Controller: autenticação e permissão

O construtor do controller (`app/controllers/CandidaturaController.php`) cria seus services, e cada service cria seus repositories; o repository pega a conexão PDO única em `ConnectionFactory::getConnection()` (singleton: uma única conexão por processo, reaproveitada por todos os repositories). Ou seja, a conexão com o banco nasce na primeira vez que algum repository precisa dela.

O método `demonstrar()` começa com `$this->trabalhadorRequired()`. Esse é o **ponto onde autenticação e permissão são verificadas**, e ele vive no controller base (`app/core/Controller.php`):

- `autenticacaoRequired()` confere se há um `Usuario` na sessão e **recarrega o usuário do banco a cada requisição** para ver se a conta ainda está ativa (RN04). Se a conta foi desativada/bloqueada desde o login, a sessão é destruída e a pessoa é mandada para `/login?motivo=conta_inativa`. Se passar, o usuário da sessão é substituído pelo recém-lido — por isso um papel alterado por um admin passa a valer sem a pessoa precisar logar de novo.
- `trabalhadorRequired()` chama `autenticacaoRequired()` e depois verifica o **papel**: só passa quem é trabalhador. Caso contrário, redireciona para `/403`.

Em seguida o controller lê `id_vaga`, converte para inteiro, confere se a vaga existe e delega para o service. Ele **não decide nenhuma regra de negócio**; só entrega a entrada e escolhe a resposta.

### 2.3 Service: a regra de negócio

`CandidaturaController::demonstrar()` chama duas coisas, nessa ordem:

1. `CandidaturaService::validarCandidatura($usuario, $vaga)` — confere, em sequência: é trabalhador; a vaga está `ATIVA`; a vaga está `visível` (não oculta/removida pela moderação); está dentro do prazo (`data_limite`); ainda há vaga disponível (`total_aceitos < trabalhadores_limite`); e que o trabalhador **ainda não se candidatou** a essa vaga (RN15, unicidade). Qualquer falha lança uma `Exception` com uma mensagem já pronta para a tela.
2. `CandidaturaService::demonstrarInteresse($idVaga, $idTrabalhador)` — repete parte dessas checagens (vaga ativa, visível, dentro do prazo, não é o dono se candidatando na própria vaga, não duplicada) e, se tudo OK, monta um objeto `Candidatura` e manda `CandidaturaRepository::criar()` persistir.

Por que a mesma coisa é checada duas vezes (em `validarCandidatura()` e dentro de `demonstrarInteresse()`, os dois no mesmo `CandidaturaService`)? Não há uma razão de design documentada — são duas checagens que foram crescendo em paralelo dentro da mesma classe. Funciona porque as duas concordam, mas é redundância, não defesa em profundidade deliberada.

Se qualquer exceção for lançada, o controller captura e grava a mensagem na sessão via `flashErro()`; se der certo, `flashSucesso('Candidatura enviada com sucesso!')`. Os dois casos **redirecionam** (padrão Post-Redirect-Get) para `/vagas/visualizar?id=X`.

### 2.4 Repository: o SQL

`CandidaturaRepository::criar(Candidatura $candidatura)` monta um `INSERT INTO candidatura (...) VALUES (...)` com `PDO::prepare()` e `bindValue()` — **toda consulta do projeto é parametrizada**, não há concatenação de SQL com entrada do usuário em lugar nenhum do código atual. Depois do insert, devolve `lastInsertId()`.

### 2.5 De volta pra view

O redirect leva a um novo `GET /vagas/visualizar?id=X`, que passa por todo o ciclo de novo (rota → `VagaController::visualizar()` → busca a vaga, o contratante e se o trabalhador logado já se candidatou → `view('vaga/vaga_show', [...])`). A view inclui `shared/header.php`, `shared/navbar.php` (que agora também inclui `shared/flash.php` — ver seção 7) e `shared/footer.php`, e usa só variáveis extraídas pelo `Controller::view()` via `extract($data)`.

---

## 3. As cinco camadas, uma por uma

| Camada | Pasta | Responsabilidade | Não faz |
|---|---|---|---|
| Core | `app/core/` | Router, autoload, Controller base (auth, redirect, flash, sanitização) | Regra de negócio |
| Controller | `app/controllers/` | Lê `$_GET`/`$_POST`, chama `autenticacaoRequired()`/`contratanteRequired()`/etc., delega pro service, escolhe a view/redirect | SQL, regra de negócio complexa |
| Service | `app/services/` | Regras de negócio (RN), transações (`beginTransaction`/`commit`/`rollBack`), orquestra repositories | HTML, `$_GET`/`$_POST` direto |
| Repository | `app/repositories/` | Uma classe por tabela principal, só SQL parametrizado via PDO | Regra de negócio |
| Model | `app/models/` | Objetos simples (getters/setters, `arrayParaObjeto()` para montar a partir de uma linha do banco) | SQL, HTML |
| View | `app/views/` | HTML + Tailwind (via CDN) + um pouco de JS inline. Recebe dados prontos do controller | Consulta ao banco, regra de negócio |

Os 6 controllers: `AutenticacaoController`, `UsuarioController`, `VagaController`, `CandidaturaController`, `DenunciaController`, `ErroController` (só a página 403).

Os 5 services: `AutenticacaoService`, `UsuarioService`, `VagaService`, `CandidaturaService`, `DenunciaService`. Cada um guarda tanto a orquestração (criar, buscar, atualizar) quanto as regras de negócio (RN) da sua própria área — não existe mais uma classe genérica à parte para isso (ver seção 5).

Os 4 repositories: `UsuarioRepository`, `VagaRepository`, `CandidaturaRepository`, `DenunciaRepository`. Repare que **não existe um `CategoriaRepository` nem `HabilidadeRepository` dedicados** — consultas de categoria/habilidade vivem dentro de `VagaRepository`/`UsuarioRepository` mesmo.

Os 5 models: `Usuario`, `Vaga`, `Candidatura`, `Denuncia`, `Habilidade`. Todos seguem o mesmo padrão: construtor posicional, getters/setters, e um `arrayParaObjeto(array $linhaDoBanco): static` estático que o repository usa para montar o objeto a partir do `PDO::fetch()`.

Os 3 helpers (`app/helpers/`), classes utilitárias sem regra de negócio própria:
- `Validador` — validação de campos de formulário, encadeável (`->obrigatorio()->email()...`), acumulando erros por campo (ver seção 5).
- `Formatador` — `Formatador::documento()` aplica a máscara de CPF (`000.000.000-00`) ou CNPJ (`00.000.000/0000-00`) conforme a quantidade de dígitos. Usado nas telas de perfil; no banco o documento fica só com dígitos.
- `UploadImagemValidador` — `UploadImagemValidador::validar($_FILES['...'])` confere se o arquivo veio mesmo do upload do PHP (`is_uploaded_file`), detecta o tipo pelo **conteúdo** (`finfo`, não pela extensão), aceita só JPG/PNG/GIF, exige que `getimagesize()` reconheça a imagem e recusa arquivos que contenham `<?php`. Não grava nada: `UsuarioService::salvarFotoPerfil()` chama o validador e depois move o arquivo para `public/uploads/perfis/` com um nome aleatório (`random_bytes`).

---

## 4. Autenticação e sessão

- Senha: `password_hash()`/`password_verify()` com bcrypt (`PASSWORD_BCRYPT`, custo 12 no cadastro).
- Sessão: cookie `httponly` sempre; `secure` só quando a requisição é HTTPS (em `http://localhost` ele seria descartado se fosse sempre `secure`); `session.use_strict_mode` ligado; `session_regenerate_id(true)` no login (previne session fixation).
- `AutenticacaoService` grava `$_SESSION['ip']` e `$_SESSION['user_agent']` no login, e tem um método estático `validarIntegridade()` que compara esses valores com a requisição atual — **mas esse método não é chamado em nenhum lugar do fluxo real** (`Controller::autenticacaoRequired()` não o usa). Ou seja: existe a validação de IP/user-agent escrita, mas ela está desconectada do fluxo de autenticação.
- A cada requisição autenticada, `autenticacaoRequired()` relê o usuário do banco (RN04) — é assim que uma conta bloqueada por um admin é desconectada na próxima ação da pessoa, não instantaneamente.
- Quem já está logado e abre `/login` ou `/cadastro` é redirecionado direto para `/vagas`.
- No login, a senha só é validada como obrigatória (não há mais checagem de tamanho mínimo ali — tamanho mínimo é regra do cadastro, não do login).

---

## 5. Regras de negócio (RN) — onde cada uma mora

A numeração "RN04", "RN17" etc. aparece em comentários espalhados pelo código; não há uma lista central numerada de RN01 a RNxx em nenhum arquivo. O que existe de fato, por função:

Cada regra mora direto no service dono do assunto — não existe mais uma classe genérica à parte para isso (até 2026-10-01 existia um `ValidadorRegrasNegocio.php` com todas elas juntas; foi eliminado e cada método foi movido pro service correspondente, porque não fazia sentido ter as regras de vaga e de candidatura centralizadas num lugar que não era nem `VagaService` nem `CandidaturaService`).

**`app/services/VagaService.php`:**
- `validarCriadorVaga()` — só contratante ativo cria vaga (RN04).
- `validarEncerramentoVaga()` — só encerra se `total_aceitos >= trabalhadores_limite` (RN05).
- `validarNovoLimite()` — ao editar, não reduz o limite abaixo da quantidade já aceita (RN10).
- `validarEdicaoVaga()` — com candidaturas existentes, título e categoria ficam travados (RN17). Esse método é o único motivo de `VagaService` depender de `CandidaturaRepository` (precisa saber se a vaga já tem candidaturas).

**`app/services/CandidaturaService.php`:**
- `validarAceitacaoCandidato()` — não aceita além do limite (RN10).
- `validarCandidatura()` — a cadeia de checagens da seção 2.3 (RN06/07/15).
- `removerInteresse()` — o trabalhador só cancela a própria candidatura enquanto ela está `PENDENTE`; se já foi aceita, a mensagem manda falar com o contratante.

Os controllers (`VagaController`, `CandidaturaController`) chamam esses métodos direto em `$this->vagaService`/`$this->service` — não existe mais uma propriedade `$validador` separada.

**`app/helpers/Validador.php`** (helper genérico de validação de formulário, não é regra de negócio pura — mistura validação de campo com algumas RN):
- `papeis()` — aplica a regra PF-só-trabalhador / PJ-só-contratante no cadastro.
- `documentoPorTipoPessoa()` — CPF para PF, CNPJ para PJ, com dígito verificador real (`cpf()`/`cnpj()` calculam os dígitos, não é só tamanho).
- `dataNaoAnterior()` — exige data **a partir de amanhã** (`valor > hoje`). É o que obriga a data do serviço de uma vaga a ser no mínimo amanhã, já que o prazo de candidatura é sempre o dia anterior (seção 6). Aceita um parâmetro `$permitida` para não travar a edição de uma vaga cuja data já gravada passou.
- `minimo()`/`maximo()` contam caracteres com `mb_strlen()`, então acentos contam como um caractere só.

**Dentro dos próprios métodos de ação dos services** (`CandidaturaService::aceitarInteressado()`, por exemplo): regras mais específicas da ação — confere dono da vaga, vaga ativa/visível, candidato ainda não aceito, e limite — em parte repetindo o que `validarAceitacaoCandidato()` já confere antes de chamar o service lá do controller.

---

## 6. Vaga: ciclo de vida

```
ATIVA ──encerrar (RN05: aceitos >= limite)──> ENCERRADA
```

Existe um método `reabrir()` em `VagaController` e em `VagaService`, mas **não há rota nem botão para ele** em `public/index.php` — na prática, vaga encerrada não volta a ficar ativa (ver seção 8).

**Prazo de candidatura automático.** O contratante não escolhe mais o prazo: o formulário só pede a **data do serviço**, e o controller calcula `data_limite = data_servico − 1 dia` ao criar e ao editar (`VagaController::criar()`/`editar()`). Por isso a data do serviço precisa ser **a partir de amanhã** (`Validador::dataNaoAnterior()`). Na edição, a data já gravada continua aceita mesmo que tenha passado, para não impedir que o dono corrija outros campos. Passado o prazo, a vaga some sozinha da listagem e da busca (`data_limite >= CURDATE()` no SQL) — não há job agendado, é só o filtro da consulta.

**Exclusão pelo dono é lógica.** `VagaRepository::deletar()` não faz mais `DELETE`: grava `excluida_em = NOW()`. Todas as consultas de vaga filtram `excluida_em IS NULL`, então para a aplicação a vaga deixa de existir, mas o registro e as candidaturas continuam no banco. Anúncio que já foi moderado (oculto/removido) não pode ser excluído pelo dono, para preservar a evidência da denúncia (`VagaService::deletar()`).

Independente de `status`, a vaga também tem `visibilidade`: `VISIVEL` (padrão) ou `REMOVIDA` (remoção pela moderação — ver seção 7). O valor `OCULTA` ainda existe no `ENUM` do banco e no código do repository, mas **nenhum fluxo da interface o produz**: só sobrou um caminho de moderação para anúncio, "Remover anúncio", que já bloqueia edição e some da listagem — ocultar-mas-deixar-editar foi removido de propósito para não ter duas ações fazendo quase a mesma coisa.

Dois campos são mantidos por **trigger** no MySQL, não em PHP:
- `trg_vaga_define_is_user_active` (BEFORE INSERT) — copia `usuario.ativo` do contratante pra `vaga.is_user_active` na criação.
- `trg_usuario_ativo_atualiza_vagas` (AFTER UPDATE em `usuario`) — se o contratante for ativado/desativado, atualiza `is_user_active` de todas as vagas dele.

Isso existe para que `VagaRepository::listar()`/`buscar()` consigam filtrar `is_user_active = 1` sem precisar de um JOIN com `usuario` toda vez.

**Busca de vagas** (`/vagas/buscar`, `VagaController::buscar()` → `VagaRepository::buscar()`): filtros por palavra-chave no título, localização, categoria, data a partir de e **remuneração mínima** (o filtro de remuneração máxima foi retirado), e ordenação por mais recentes, maior salário ou menor salário. A ordenação vem de uma lista fechada (`recentes`, `maior_salario`, `menor_salario`) e é traduzida para SQL por um `match` — o texto do usuário nunca entra no `ORDER BY`. Filtro inválido (data ou valor mal formatado) é ignorado com um aviso, em vez de bloquear a busca. A busca só mostra vagas que ainda têm vaga sobrando (`HAVING trabalhadores_limite > COUNT(aceitos)`).

**Campos da vaga** (tabela `vaga`): categoria, título, descrição, **bairro** + localização (endereço livre — dois campos distintos, não confundir), remuneração, data de publicação, prazo para candidatura (`data_limite`, calculado automaticamente; no banco a coluna aceita `NULL` por causa de vagas antigas dos dados de exemplo), data do serviço (`data_servico`), horário, duração, observações, limite de trabalhadores, status, visibilidade. O sistema trata **só trabalho temporário** — não existe mais a distinção fixo/temporário que existia numa versão anterior do schema (coluna `tipo_servico` foi removida).

---

## 7. Candidatura e moderação de denúncias

**Candidatura:** `PENDENTE` → `ACEITO` (contratante aceita, respeitando o limite) — não há estado de "recusado" explícito; o contratante simplesmente não aceita.

- **Cancelar candidatura:** enquanto está `PENDENTE`, o trabalhador pode desistir pelo mesmo botão da página da vaga (`POST /candidatura/remover` → `CandidaturaService::removerInteresse()`). Isso apaga a linha da candidatura (`DELETE`). Depois de aceita, não dá mais para cancelar pelo sistema.
- **Contato pelo WhatsApp:** na lista de candidatos (`interesse/candidatos_list.php`), o contratante vê um link `wa.me` para cada candidato, com o telefone limpo (só dígitos, com `55` na frente) e uma mensagem pronta citando a vaga — uma mensagem para aceito ("sua candidatura foi aceita") e outra para pendente ("vi sua candidatura"). O link some só quando o candidato está pendente e a vaga já lotou (aparece como "Negado"). Ou seja: o contratante pode chamar um candidato antes de aceitá-lo; a liberação de contato "só depois da seleção" vale para o lado do trabalhador.
- **Histórico do trabalhador** (`/candidatura/historico`): se o contratante excluir a vaga, a candidatura pendente some do histórico; quem já tinha sido aceito continua vendo o card, com um aviso de que o serviço foi cancelado.

**Denúncia:** `PENDENTE` → `ANALISADA`, com `acao_moderacao` registrando o que foi feito: `NENHUMA` (arquivada sem sanção), `BLOQUEIO` (conta bloqueada), `VAGA_OCULTA`/`VAGA_REMOVIDA` (só `VAGA_REMOVIDA` é alcançável hoje — ver acima). O botão no painel do admin (`/admin/denuncias`) chama isso de **"Arquivar"** (antes era rotulado "Analisar"; o nome foi trocado, o valor interno `acao_moderacao = 'NENHUMA'` continua o mesmo).

Denúncia pode ser de **anúncio** (`id_vaga_denunciada` preenchido, `id_usuario_denunciado` nulo) ou de **usuário** (o contrário) — nunca os dois ao mesmo tempo, exceto num terceiro caso: **não comparecimento** (RN17), onde o contratante denuncia o trabalhador que ele mesmo aceitou e que não apareceu — aí sim os dois campos vêm preenchidos (usuário + vaga), com motivo fixo `NAO_COMPARECIMENTO`. Esse fluxo não tem mais a trava de "só depois da data do serviço" — foi removida de propósito, a pedido explícito, para o contratante poder registrar a qualquer momento depois de aceitar.

A lista do admin (`/admin/denuncias`) mostra **nome** do denunciante/denunciado (não mais o ID cru), buscados em lote por `UsuarioRepository::buscarPorIds()`/`VagaRepository::buscarPorIds()` para não gerar uma consulta por linha.

---

## 8. Pontos que merecem atenção (não são lendas, foram conferidos)

Esta seção é sincera de propósito — é o tipo de coisa que, se a banca perguntar "e isso aqui?", você quer já saber a resposta em vez de ser pego de surpresa.

- **`Usuario::getLocalizacao()` é hardcoded.** Sempre devolve a string `"Foz do Iguaçu, PR"`, não importa o que o usuário cadastrou (`app/models/Usuario.php:58-61`). O campo "Localização" foi removido dos formulários de perfil porque editá-lo não tinha efeito nenhum na tela.
- **`Usuario::setLocalizacao()` está morto e quebrado.** Atribui a `$this->localizacao`, uma propriedade que **não existe** na classe (`app/models/Usuario.php:159-163`). Não quebra nada hoje porque nenhum código chama esse método — mas se alguém chamar, é erro de propriedade dinâmica (aviso no PHP 8.2+).
- **"Reabrir vaga" não está ligado a nada.** `VagaController::reabrir()` e `VagaService::reabrir()` existem, mas não há rota `/vagas/reabrir` em `public/index.php` nem botão em nenhuma view. Vaga encerrada fica encerrada.
- **A confirmação de senha no cadastro só é conferida no navegador.** O controller ainda exige que o campo `confirma_senha` seja preenchido, mas a comparação "senha == confirmação" foi retirada do PHP e ficou só no JavaScript de `cadastro.php`. Quem enviar o formulário sem JavaScript consegue cadastrar com as duas senhas diferentes (vale a primeira).
- **Upload de foto sem limite de tamanho na aplicação.** Quando a validação foi movida para `UploadImagemValidador`, os limites antigos (2 MB, 4096 × 4096 px) não foram levados junto. Hoje o único limite é o `upload_max_filesize`/`post_max_size` do `php.ini` do servidor.
- **A proteção da pasta de uploads está desativada.** O arquivo `public/uploads/.htaccess` (que proíbe executar `.php` e listar a pasta) foi renomeado para `.htaccess_backup` na versão final, e com esse nome o Apache o ignora. A defesa que sobra é o `UploadImagemValidador` (tipo pelo conteúdo, nome aleatório com extensão de imagem, recusa de `<?php`). Se o servidor aceitar o `.htaccess`, o ideal é voltar o nome original.
- **`OCULTA`/`VAGA_OCULTA` ainda existem no banco e no código**, mas nenhum botão produz esses valores (seção 6).
- **`AutenticacaoService::validarIntegridade()` nunca é chamado** pelo fluxo real de autenticação (ver seção 4) — existe, grava os dados na sessão no login, mas ninguém lê para invalidar a sessão.
- **Muito `error_log()` de debug espalhado**, principalmente em `CandidaturaService` (14 chamadas, fluxo de aceitar candidato) e `AutenticacaoService` (5, login) — escrevem o estado inteiro de objetos a cada requisição. Não quebram nada, mas são ruído de performance e de log; foram deixados de uma fase de depuração.
- **Banco de dev sem persistência de schema fora do `script.sql`.** Não há sistema de migrations versionado — `app/database/scripts/script.sql` é a fonte única da verdade do schema e dos dados de exemplo; recriar o banco é "DROP DATABASE + rodar o script inteiro de novo", não um histórico incremental.

---

## 9. Banco de dados

7 tabelas: `usuario`, `categoria`, `habilidade`, `usuario_habilidade` (N:N), `vaga`, `candidatura`, `denuncia`. 2 triggers (seção 6).

Pontos de design que valem explicar numa arguição:
- **Uma pessoa é um único `usuario`, com até três flags booleanas** (`is_admin`, `is_trabalhador`, `is_contratante`) em vez de uma tabela de papéis separada. Duas constraints de banco reforçam isso: `chk_usuario_tem_papel` (pelo menos um papel) e `chk_usuario_pj_papel_unico` (PJ nunca pode ser trabalhador **e** contratante ao mesmo tempo). A regra mais nova — PF só trabalhador, PJ só contratante — é aplicada na camada de aplicação (`Validador::papeis()`), não como constraint de banco.
- **`documento`** guarda CPF (PF) ou CNPJ (PJ) no mesmo campo; `tipo_pessoa` diz qual é qual.
- **Denúncias nunca são apagadas fisicamente** — ficam como evidência. **Vagas também não**: exclusão pelo dono grava `excluida_em` e remoção pela moderação vira `visibilidade = 'REMOVIDA'`; nos dois casos as candidaturas da vaga continuam no banco. A única exclusão física é a da **candidatura pendente cancelada pelo próprio trabalhador** (seção 7).
- Senhas de todos os usuários de exemplo no `script.sql`: `senha123` (hash bcrypt já gravado).

---

## 10. Como o usuário sabe o que aconteceu (mensagens flash)

Boa parte do trabalho recente foi garantir que toda ação com `POST` + redirect avise a pessoa do resultado — antes, várias ações falhavam ou tinham sucesso em silêncio, sem nada visível na tela. O mecanismo:

- `Controller::flashSucesso(string $msg)` / `Controller::flashErro(string $msg)` gravam a mensagem em `$_SESSION['flash_sucesso']`/`$_SESSION['flash_erro']`.
- `app/views/shared/flash.php`, incluído logo após a navbar em toda página (`navbar.php`), lê e **apaga** essas chaves da sessão — por isso a mensagem aparece uma única vez, na primeira página carregada depois do redirect.
- Está conectado em: aceitar candidato, candidatar-se, cancelar candidatura, criar/editar/excluir/encerrar vaga, moderação de denúncia (bloquear/remover/arquivar), formulário de não comparecimento.

---

## 11. Se a banca pedir para rodar o sistema

- **Credenciais do admin:** `admin@freelaja.com` / `senha123`.
- **Usuários de exemplo:** `trabalhador@freelaja.com` (trabalhador) e `contratante@freelaja.com` (contratante), também com `senha123`.
- `app/config/Config.php` tem as credenciais do banco direto no código (não há `.env`). No repositório elas estão vazias: preencha `DB_HOST`, `DB_NAME`, `DB_USER` e `DB_PASS` antes de rodar.
- **O banco se cria sozinho.** Na primeira requisição, se a conexão com `DB_NAME` falhar, `ConnectionFactory` conecta no MySQL sem banco e chama `DatabaseInitializer`, que faz `CREATE DATABASE IF NOT EXISTS` e executa `app/database/scripts/script.sql` (schema, triggers e dados de exemplo). Para isso o usuário do MySQL precisa de permissão para criar banco; se não tiver, importe o script manualmente num banco vazio.
- Para subir rápido sem Apache: `php -S localhost:8080 -t public`. O passo a passo completo está no `README.md`.
