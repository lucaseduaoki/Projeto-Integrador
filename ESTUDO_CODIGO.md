# FreelaJá: guia para entender e defender o código

Este documento existe para que você consiga explicar o sistema de cabeça, sem decorar arquivo por arquivo. Tudo o que está aqui foi conferido direto no código do repositório e testado rodando a aplicação de verdade; onde algo não pôde ser confirmado, está escrito "não confirmado". As referências entre parênteses (`arquivo:linha` ou só `arquivo`) servem para você abrir e conferir.

Uma advertência de método: os números de linha mudam quando alguém edita o arquivo. Se uma linha citada não bater, procure o nome do método ao lado dela.

---

## 1. Visão geral em uma página

**O que é.** O FreelaJá é uma plataforma web que conecta quem precisa de um serviço pontual (o **contratante**) a quem presta esse serviço (o **trabalhador**). O contratante publica uma **vaga** (anúncio), trabalhadores se **candidatam**, o contratante **aceita** quem quiser (respeitando um limite de vagas) e só então os dados de contato ficam visíveis entre as partes. Um **administrador** modera a plataforma por meio de **denúncias**. O sistema não tem pagamento: ele apenas aproxima as partes.

**Regra de papéis (importante, foi reforçada recentemente).** O cadastro não deixa mais o usuário escolher livremente: **pessoa física (CPF) é sempre trabalhador**, **pessoa jurídica (CNPJ) é sempre contratante**. Essa é uma regra de negócio explícita — não existe mais acúmulo de papéis nem conversão de um para o outro depois do cadastro.

**Decisão arquitetural central.** É um monólito em **PHP puro** com **MVC próprio**, sem framework, sem Composer e sem ORM. O roteador, o carregador de classes e o controller base foram escritos pela equipe (`app/core/`). A regra que organiza tudo é a divisão em camadas com uma responsabilidade cada: o **controller** recebe a requisição, autentica/autoriza e valida a entrada; o **service** decide as regras de negócio (RN); o **repository** fala SQL; o **model** representa os dados; e a **view** só desenha HTML. O banco é **MySQL** acessado por **PDO** com consultas parametrizadas.

**Tamanho (conferido em 2026-10-01).** 53 arquivos PHP, cerca de 9.000 linhas. 33 rotas, 7 tabelas, 2 triggers. 6 controllers, 6 services, 4 repositories, 5 models, 23 views. Não há testes automatizados.

**Se a banca pedir "descreva seu sistema", uma resposta de cabeça:** *"É uma plataforma de vagas de serviços pontuais em PHP com MVC próprio. Toda requisição entra por um único arquivo, `public/index.php`, que registra as rotas; o roteador escolhe um controller; o controller confere quem é o usuário e valida a entrada; o service aplica as regras de negócio, como limite de trabalhadores aceitos, candidatura duplicada e liberação de contato só depois da seleção; o repository executa SQL parametrizado no MySQL; e uma view renderiza o resultado. Pessoa física só pode ser trabalhador, pessoa jurídica só pode ser contratante — sem acúmulo. A moderação é feita por denúncias: bloqueio de conta, remoção lógica de anúncio ou arquivamento sem sanção, sem apagar dados."*

---

## 2. O caminho de uma requisição

O melhor jeito de entender a estrutura é seguir uma requisição real do começo ao fim. Vamos acompanhar este caso: **um trabalhador clica em "Candidatar-se" na página de uma vaga**.

### 2.1 Da porta de entrada ao roteador

1. O navegador envia `POST /candidatura/demonstrar` com o campo `id_vaga`. O formulário está em `app/views/vaga/vaga_show.php`.
2. **`public/.htaccess`** reescreve qualquer caminho que não seja um arquivo/diretório real para `public/index.php`. Por isso existe um único ponto de entrada.
3. **`public/index.php`** faz, nesta ordem: carrega o autoload (`app/core/Autoload.php`), carrega a configuração (`app/config/Config.php`), registra um tratador global de exceções (`set_exception_handler`), cria o `Router`, declara as 33 rotas e chama `$router->run()`.
   - O **autoload** converte um nome de classe em caminho de arquivo: `app\services\VagaService` vira `app/services/VagaService.php`. É por isso que os namespaces espelham as pastas.
   - O **`Config.php`** define as constantes de banco **diretamente no código** (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` — não há mais `.env`, foi removido de propósito), configura a sessão (cookie `httponly`, `secure` só se a requisição for HTTPS, `use_strict_mode`), define o fuso `America/Sao_Paulo`, inicia a sessão e calcula `URL_BASE` a partir do host da requisição.
4. **`app/core/Router.php`.** O método `run()` normaliza a URI, procura uma rota cujo caminho **e** método HTTP batam exatamente, e chama `dispatch()`. O roteador só faz correspondência exata: **não existe parâmetro na URL**. Por isso os identificadores viajam sempre na query string (`?id=3`) ou no corpo do POST (`id_vaga`). Se nada casar, renderiza `errors/404.php`. Ao casar, `dispatch()` faz `new CandidaturaController` e chama o método `demonstrar`.

### 2.2 Controller: autenticação e permissão

O construtor do controller (`app/controllers/CandidaturaController.php`) cria seus services, e cada service cria seus repositories; o repository pega a conexão PDO única em `ConnectionFactory::getConnection()` (singleton: uma única conexão por processo, reaproveitada por todos os repositories). Ou seja, a conexão com o banco nasce na primeira vez que algum repository precisa dela.

O método `demonstrar()` começa com `$this->trabalhadorRequired()`. Esse é o **ponto onde autenticação e permissão são verificadas**, e ele vive no controller base (`app/core/Controller.php`):

- `autenticacaoRequired()` confere se há um `Usuario` na sessão e **recarrega o usuário do banco a cada requisição** para ver se a conta ainda está ativa (RN04). Se a conta foi desativada/bloqueada desde o login, a sessão é destruída e a pessoa é mandada para `/login?motivo=conta_inativa`. Se passar, o usuário da sessão é substituído pelo recém-lido — por isso um papel alterado por um admin passa a valer sem a pessoa precisar logar de novo.
- `trabalhadorRequired()` chama `autenticacaoRequired()` e depois verifica o **papel**: só passa quem é trabalhador. Caso contrário, redireciona para `/403`.

Em seguida o controller lê `id_vaga`, converte para inteiro, confere se a vaga existe e delega para o service. Ele **não decide nenhuma regra de negócio**; só entrega a entrada e escolhe a resposta.

### 2.3 Service: a regra de negócio

`CandidaturaController::demonstrar()` chama duas coisas, nessa ordem:

1. `ValidadorRegrasNegocio::validarCandidatura($usuario, $vaga)` — confere, em sequência: é trabalhador; a vaga está `ATIVA`; a vaga está `visível` (não oculta/removida pela moderação); está dentro do prazo (`data_limite`); ainda há vaga disponível (`total_aceitos < trabalhadores_limite`); e que o trabalhador **ainda não se candidatou** a essa vaga (RN15, unicidade). Qualquer falha lança uma `Exception` com uma mensagem já pronta para a tela.
2. `CandidaturaService::demonstrarInteresse($idVaga, $idTrabalhador)` — repete parte dessas checagens (vaga ativa, visível, dentro do prazo, não é o dono se candidatando na própria vaga, não duplicada) e, se tudo OK, monta um objeto `Candidatura` e manda `CandidaturaRepository::criar()` persistir.

Por que a mesma coisa é checada duas vezes (no validador e no service)? Não há uma razão de design documentada — são duas camadas de validação que foram crescendo em paralelo. Funciona porque as duas concordam, mas é redundância, não defesa em profundidade deliberada.

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

Os 6 services: `AutenticacaoService`, `UsuarioService`, `VagaService`, `CandidaturaService`, `DenunciaService`, e `ValidadorRegrasNegocio` (concentra as regras numeradas RN04 a RN17 — ver seção 5).

Os 4 repositories: `UsuarioRepository`, `VagaRepository`, `CandidaturaRepository`, `DenunciaRepository`. Repare que **não existe um `CategoriaRepository` nem `HabilidadeRepository` dedicados** — consultas de categoria/habilidade vivem dentro de `VagaRepository`/`UsuarioRepository` mesmo.

Os 5 models: `Usuario`, `Vaga`, `Candidatura`, `Denuncia`, `Habilidade`. Todos seguem o mesmo padrão: construtor posicional, getters/setters, e um `arrayParaObjeto(array $linhaDoBanco): static` estático que o repository usa para montar o objeto a partir do `PDO::fetch()`.

---

## 4. Autenticação e sessão

- Senha: `password_hash()`/`password_verify()` com bcrypt (`PASSWORD_BCRYPT`, custo 12 no cadastro).
- Sessão: cookie `httponly` sempre; `secure` só quando a requisição é HTTPS (em `http://localhost` ele seria descartado se fosse sempre `secure`); `session.use_strict_mode` ligado; `session_regenerate_id(true)` no login (previne session fixation).
- `AutenticacaoService` grava `$_SESSION['ip']` e `$_SESSION['user_agent']` no login, e tem um método estático `validarIntegridade()` que compara esses valores com a requisição atual — **mas esse método não é chamado em nenhum lugar do fluxo real** (`Controller::autenticacaoRequired()` não o usa). Ou seja: existe a validação de IP/user-agent escrita, mas ela está desconectada do fluxo de autenticação.
- A cada requisição autenticada, `autenticacaoRequired()` relê o usuário do banco (RN04) — é assim que uma conta bloqueada por um admin é desconectada na próxima ação da pessoa, não instantaneamente.

---

## 5. Regras de negócio (RN) — onde cada uma mora

A numeração "RN04", "RN17" etc. aparece em comentários espalhados pelo código; não há uma lista central numerada de RN01 a RNxx em nenhum arquivo. O que existe de fato, por função:

**`app/services/ValidadorRegrasNegocio.php`** (classe dedicada, usada por `VagaController` e `CandidaturaController`):
- `validarCriadorVaga()` — só contratante ativo cria vaga (RN04).
- `validarEncerramentoVaga()` — só encerra se `total_aceitos >= trabalhadores_limite` (RN05).
- `validarAceitacaoCandidato()` — não aceita além do limite (RN10).
- `validarNovoLimite()` — ao editar, não reduz o limite abaixo da quantidade já aceita (RN10).
- `validarCandidatura()` — a cadeia de checagens da seção 2.3 (RN06/07/15).
- `validarEdicaoVaga()` — com candidaturas existentes, título e categoria ficam travados (RN17).

**`app/helpers/Validador.php`** (helper genérico de validação de formulário, não é regra de negócio pura — mistura validação de campo com algumas RN):
- `papeis()` — aplica a regra PF-só-trabalhador / PJ-só-contratante no cadastro.
- `documentoPorTipoPessoa()` — CPF para PF, CNPJ para PJ, com dígito verificador real (`cpf()`/`cnpj()` calculam os dígitos, não é só tamanho).
- `responsavelPrestadora()` — ainda existe no código mas hoje é inatingível: só dispara para "PJ que presta serviço", estado que a regra de papéis atual não permite mais criar. Ver seção 8.

**Dentro dos services** (`CandidaturaService`, `DenunciaService`, `VagaService`): regras mais específicas de cada ação — por exemplo, `CandidaturaService::aceitarInteressado()` confere dono da vaga, vaga ativa/visível, candidato ainda não aceito, e limite — em parte repetindo o que `ValidadorRegrasNegocio` já confere antes de chamar o service.

---

## 6. Vaga: ciclo de vida

```
ATIVA ──encerrar (RN05: aceitos >= limite)──> ENCERRADA ──reabrir──> ATIVA
```

Independente de `status`, a vaga também tem `visibilidade`: `VISIVEL` (padrão) ou `REMOVIDA` (exclusão lógica pela moderação — ver seção 7). **Não existe mais o estado `OCULTA`** no fluxo normal: só sobrou um caminho de moderação para anúncio, "Remover anúncio", que já bloqueia edição e some da listagem — ocultar-mas-deixar-editar foi removido de propósito para não ter duas ações fazendo quase a mesma coisa.

Dois campos são mantidos por **trigger** no MySQL, não em PHP:
- `trg_vaga_define_is_user_active` (BEFORE INSERT) — copia `usuario.ativo` do contratante pra `vaga.is_user_active` na criação.
- `trg_usuario_ativo_atualiza_vagas` (AFTER UPDATE em `usuario`) — se o contratante for ativado/desativado, atualiza `is_user_active` de todas as vagas dele.

Isso existe para que `VagaRepository::listar()`/`buscar()` consigam filtrar `is_user_active = 1` sem precisar de um JOIN com `usuario` toda vez.

**Campos da vaga** (tabela `vaga`): categoria, título, descrição, **bairro** + localização (endereço livre — dois campos distintos, não confundir), remuneração, data de publicação, prazo para candidatura (`data_limite`, opcional), data do serviço (`data_servico`), horário, duração, observações, limite de trabalhadores, status, visibilidade. O sistema trata **só trabalho temporário** — não existe mais a distinção fixo/temporário que existia numa versão anterior do schema (coluna `tipo_servico` foi removida).

---

## 7. Candidatura e moderação de denúncias

**Candidatura:** `PENDENTE` → `ACEITO` (contratante aceita, respeitando o limite) — não há estado de "recusado" explícito; o contratante simplesmente não aceita.

**Denúncia:** `PENDENTE` → `ANALISADA`, com `acao_moderacao` registrando o que foi feito: `NENHUMA` (arquivada sem sanção), `BLOQUEIO` (conta bloqueada), `VAGA_OCULTA`/`VAGA_REMOVIDA` (só `VAGA_REMOVIDA` é alcançável hoje — ver acima). O botão no painel do admin (`/admin/denuncias`) chama isso de **"Arquivar"** (antes era rotulado "Analisar"; o nome foi trocado, o valor interno `acao_moderacao = 'NENHUMA'` continua o mesmo).

Denúncia pode ser de **anúncio** (`id_vaga_denunciada` preenchido, `id_usuario_denunciado` nulo) ou de **usuário** (o contrário) — nunca os dois ao mesmo tempo, exceto num terceiro caso: **não comparecimento** (RN17), onde o contratante denuncia o trabalhador que ele mesmo aceitou e que não apareceu — aí sim os dois campos vêm preenchidos (usuário + vaga), com motivo fixo `NAO_COMPARECIMENTO`. Esse fluxo não tem mais a trava de "só depois da data do serviço" — foi removida de propósito, a pedido explícito, para o contratante poder registrar a qualquer momento depois de aceitar.

A lista do admin (`/admin/denuncias`) mostra **nome** do denunciante/denunciado (não mais o ID cru), buscados em lote por `UsuarioRepository::buscarPorIds()`/`VagaRepository::buscarPorIds()` para não gerar uma consulta por linha.

---

## 8. Pontos que merecem atenção (não são lendas, foram conferidos)

Esta seção é sincera de propósito — é o tipo de coisa que, se a banca perguntar "e isso aqui?", você quer já saber a resposta em vez de ser pego de surpresa.

- **`Usuario::getLocalizacao()` é hardcoded.** Sempre devolve a string `"Foz do Iguaçu, PR"`, não importa o que o usuário cadastrou (`app/models/Usuario.php:58-61`). O campo "Localização" foi removido dos formulários de perfil porque editá-lo não tinha efeito nenhum na tela.
- **`Usuario::setLocalizacao()` está morto e quebrado.** Atribui a `$this->localizacao`, uma propriedade que **não existe** na classe (`app/models/Usuario.php:159-163`). Não quebra nada hoje porque nenhum código chama esse método — mas se alguém chamar, é erro de propriedade dinâmica (aviso no PHP 8.2+).
- **`Validador::responsavelPrestadora()` está desconectado.** Só faz sentido para "PJ que presta serviço", um estado que a regra atual (PF-trabalhador / PJ-contratante, sem exceção) não deixa mais criar por nenhum fluxo da aplicação. O método continua ali, sem uso.
- **`AutenticacaoService::validarIntegridade()` nunca é chamado** pelo fluxo real de autenticação (ver seção 4) — existe, grava os dados na sessão no login, mas ninguém lê para invalidar a sessão.
- **Muito `error_log()` de debug espalhado**, principalmente em `CandidaturaService` (fluxo de aceitar candidato) e `AutenticacaoService` (login) — escrevem o estado inteiro de objetos a cada requisição. Não quebram nada, mas são ruído de performance e de log; foram deixados de uma fase de depuração.
- **Banco de dev sem persistência de schema fora do `script.sql`.** Não há sistema de migrations versionado — `app/database/scripts/script.sql` é a fonte única da verdade do schema e dos dados de exemplo; recriar o banco é "DROP DATABASE + rodar o script inteiro de novo", não um histórico incremental.

---

## 9. Banco de dados

7 tabelas: `usuario`, `categoria`, `habilidade`, `usuario_habilidade` (N:N), `vaga`, `candidatura`, `denuncia`. 2 triggers (seção 6).

Pontos de design que valem explicar numa arguição:
- **Uma pessoa é um único `usuario`, com até três flags booleanas** (`is_admin`, `is_trabalhador`, `is_contratante`) em vez de uma tabela de papéis separada. Duas constraints de banco reforçam isso: `chk_usuario_tem_papel` (pelo menos um papel) e `chk_usuario_pj_papel_unico` (PJ nunca pode ser trabalhador **e** contratante ao mesmo tempo). A regra mais nova — PF só trabalhador, PJ só contratante — é aplicada na camada de aplicação (`Validador::papeis()`), não como constraint de banco.
- **`documento`** guarda CPF (PF) ou CNPJ (PJ) no mesmo campo; `tipo_pessoa` diz qual é qual.
- **Candidaturas e denúncias nunca são apagadas fisicamente** — ficam como histórico/evidência. Vagas removidas pela moderação também: viram `visibilidade = 'REMOVIDA'`, nunca um `DELETE`.
- Senhas de todos os usuários de exemplo no `script.sql`: `senha123` (hash bcrypt já gravado).

---

## 10. Como o usuário sabe o que aconteceu (mensagens flash)

Boa parte do trabalho recente foi garantir que toda ação com `POST` + redirect avise a pessoa do resultado — antes, várias ações falhavam ou tinham sucesso em silêncio, sem nada visível na tela. O mecanismo:

- `Controller::flashSucesso(string $msg)` / `Controller::flashErro(string $msg)` gravam a mensagem em `$_SESSION['flash_sucesso']`/`$_SESSION['flash_erro']`.
- `app/views/shared/flash.php`, incluído logo após a navbar em toda página (`navbar.php`), lê e **apaga** essas chaves da sessão — por isso a mensagem aparece uma única vez, na primeira página carregada depois do redirect.
- Está conectado em: aceitar/recusar candidato, candidatar-se, criar/editar/excluir/encerrar/reabrir vaga, moderação de denúncia (bloquear/remover/arquivar), formulário de não comparecimento.

---

## 11. Se a banca pedir para rodar o sistema

- **Credenciais do admin:** `admin@freelaja.com` / `senha123`.
- O schema + dados de exemplo inteiros estão em `app/database/scripts/script.sql`; rodar esse script contra um banco `freelaja` vazio recria tudo.
- `app/config/Config.php` tem as credenciais do banco direto no código (não há `.env`).
