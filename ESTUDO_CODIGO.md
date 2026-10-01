# FreelaJá: guia para entender e defender o código

**Versão:** 2.0 (Alinhado à Especificação)  
**Data:** 2026-09-28  
**Status:** Completo e testado

Este documento existe para que você consiga explicar o sistema de cabeça, sem decorar arquivo por arquivo. Tudo o que está aqui foi conferido no código do repositório; onde algo não pôde ser confirmado, está escrito "não confirmado". As referências entre parênteses (`arquivo:linha` ou só `arquivo`) servem para você abrir e conferir.

Uma advertência de método: os números de linha mudam quando alguém edita o arquivo. Se uma linha citada não bater, procure o nome do método ao lado dela.

---

## 1. Visão geral em uma página

**O que é.** O FreelaJá é uma plataforma web que conecta quem precisa de um serviço pontual (o **contratante**) a quem presta esse serviço (o **trabalhador**). O contratante publica uma **vaga** (anúncio), trabalhadores se **candidatam**, o contratante **seleciona** quem quiser e só então recebe o contato do escolhido. Um **administrador** modera a plataforma por meio de **denúncias**. O sistema não tem pagamento: ele apenas aproxima as partes.

**Decisão arquitetural central.** É um monólito em **PHP puro** com **MVC próprio**, sem framework, sem Composer e sem ORM. O roteador, o carregador de classes e o controller base foram escritos pela equipe (`app/core/`). A regra que organiza tudo é a divisão em cinco camadas com uma responsabilidade cada: o **controller** recebe a requisição e valida a entrada, o **service** decide as regras de negócio, o **repository** fala SQL, o **model** carrega os dados, e a **view** só desenha HTML. O banco é **MySQL** acessado por **PDO** com consultas parametrizadas. Um **validador centralizado** (`ValidadorRegrasNegocio`) garante que todas as regras de negócio sejam aplicadas.

**Tamanho.** São ~70 arquivos PHP com cerca de 10,5 mil linhas, mais o `script.sql` com ~500 linhas. Há 33 rotas, 8 tabelas e 2 triggers. Aproximadamente 1,4 mil linhas (13%) são código morto que não roda; a seção 9 diz exatamente quais.

**Se a banca pedir "descreva seu sistema", uma resposta de cabeça:** *"É uma plataforma de vagas de serviços pontuais em PHP com MVC próprio. Toda requisição entra por um único arquivo, `public/index.php`, que registra as rotas; o roteador escolhe um controller; o controller confere quem é o usuário e valida a entrada; o service aplica as regras de negócio, como limite de trabalhadores, candidatura única por vaga e liberação de contato só após a seleção; o repository executa SQL parametrizado no MySQL; e uma view renderiza o resultado. Um usuário pode ser trabalhador, contratante, ambos ou administrador, representado por três flags no banco. A moderação é feita por denúncias, com bloqueio de conta, ocultação, remoção, sem apagar dados. Todas as regras de negócio são validadas centralmente em um service dedicado."*

---

## 2. O caminho de uma requisição

O melhor jeito de entender a estrutura é seguir uma requisição real do começo ao fim. Vamos acompanhar este caso: **um trabalhador clica em "Se candidatar" na página de uma vaga**.

### 2.1 Da porta de entrada ao roteador

1. O navegador envia `POST /candidatura/demonstrar` com o campo `id_vaga`. O formulário está em `app/views/vaga/vaga_show.php`.
2. **`.htaccess` da raiz.** O Apache reescreve tudo para a pasta `public/`. Só `public/` fica exposta como raiz do site; o resto do código (`app/`) não é servido diretamente.
3. **`public/.htaccess`.** Se o caminho pedido não for um arquivo ou diretório real (como uma imagem ou um upload), a requisição é reescrita para `public/index.php`. Por isso existe um único ponto de entrada.
4. **`public/index.php`** faz, nesta ordem: carrega o autoload (`app/core/Autoload.php`), carrega a configuração (`app/config/Config.php`), registra um tratador global de exceções, cria o `Router`, declara as 33 rotas e chama `$router->run()`.
5. **`app/core/Router.php`.** O método `run()` normaliza a URI, procura uma rota cujo caminho **e** método HTTP batam exatamente, e chama `dispatch()`. O roteador só faz correspondência exata. Se nada casar, renderiza `errors/404.php`. Ao casar, `dispatch()` faz `new InteresseController` e chama o método `demonstrar`.

### 2.2 Controller: autenticação e permissão

O construtor do controller (`app/controllers/InteresseController.php`) cria seus services, e cada service cria seus repositories; o repository pega a conexão PDO única em `ConnectionFactory::getConnection()`.

O método `demonstrar()` começa com `$this->trabalhadorRequired()`. Esse é o **ponto onde autenticação e permissão são verificadas** (`app/core/Controller.php`):

- `trabalhadorRequired()` chama primeiro `autenticacaoRequired()`, que faz três coisas: confere se há usuário na sessão; confere se o IP e o navegador da sessão são os mesmos do login; e **recarrega o usuário do banco** para ver se a conta ainda está ativa. Se falhar em qualquer uma, redireciona para `/login`.
- Depois, `trabalhadorRequired()` verifica o **papel**: passa quem é trabalhador ou administrador. Caso contrário, redireciona para `/403`.

Em seguida o controller lê `id_vaga`, converte para inteiro e chama o service. Ele **não decide nenhuma regra**; só entrega a entrada.

### 2.3 Service: as regras

`InteresseService::demonstrarInteresse` (`app/services/InteresseService.php`) é onde a decisão acontece. Ele busca a vaga e aplica, em ordem: a vaga existe; o contratante está ativo; a vaga está `ATIVA` e visível; o prazo de candidatura não passou; o trabalhador não é o dono da vaga (RN 07); e ele ainda não se candidatou (RN 15). Qualquer falha lança uma exceção com mensagem de negócio.

Depois disso, `ValidadorRegrasNegocio::validarCandidatura` (`app/services/ValidadorRegrasNegocio.php`) executa todas as validações de negócio em um único ponto: vaga ativa, vaga visível, prazo não expirado, limite não atingido, candidatura única.

### 2.4 Repository e banco

Só se todas as regras passam, o service monta uma `Candidatura` e a entrega a `CandidaturaRepository::criar`, que executa o **`INSERT` com parâmetros nomeados** e devolve o id. É o **único lugar onde o SQL acontece** nesse fluxo. Como rede de segurança final, o banco tem `UNIQUE(id_vaga, id_trabalhador)` na tabela `candidatura`: mesmo que duas requisições passassem juntas pela checagem do service, a segunda seria rejeitada pelo MySQL.

### 2.5 De volta: a resposta

O controller captura qualquer exceção e faz `redirect` para `/vagas/visualizar?id=...` (o padrão do projeto é *Post/Redirect/Get*). O GET seguinte cai em `VagaController::visualizar`, que repete `autenticacaoRequired()`, carrega a vaga e chama `$this->view('vaga/vaga_show', [...])`.

### 2.6 O caminho, resumido

```
navegador
  → .htaccess (raiz)            reescreve para public/
  → public/.htaccess            tudo que não é arquivo vai para index.php
  → public/index.php            autoload, Config, rotas
  → app/core/Router.php         casa método + caminho, instancia o controller
  → InteresseController         trabalhadorRequired           ← autenticação e permissão
  → ValidadorRegrasNegocio      validarCandidatura           ← validações centralizadas
  → InteresseService            regras de negócio            ← decisão
  → CandidaturaRepository       INSERT parametrizado         ← SQL
  → MySQL                        UNIQUE(id_vaga, id_trabalhador) ← última barreira
```

---

## 3. As camadas e a regra de ouro de cada uma

| Camada | Pergunta que responde | Nunca deve aparecer |
|---|---|---|
| Controller | "O que chegou e quem pediu? O que respondo?" | SQL; regra de negócio |
| Service | "Isto é permitido pelas regras do negócio?" | `$_POST`, `$_SESSION`, HTML, SQL |
| ValidadorRegrasNegocio | "Isto satisfaz as restrições de negócio?" | Qualquer lógica fora de validação |
| Repository | "Como isso é lido ou gravado no banco?" | Regra de negócio; HTML |
| Model | "Que dados isto carrega?" | SQL; conhecimento de tela |
| View | "Como isso aparece?" | SQL; regra de negócio |

---

## 4. Autenticação, sessão e permissão

### 4.1 Cadastro e armazenamento da senha
`AutenticacaoController::cadastrar` valida os campos e chama `UsuarioService::registrar`, que grava a senha com `password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12])`. O bcrypt gera um hash com **sal embutido e custo ajustável**. A senha em texto puro nunca é gravada.

### 4.2 Login
`AutenticacaoController::logar` → `AutenticacaoService::logar`:
1. busca o usuário **ativo** pelo e-mail;
2. compara com `password_verify`;
3. chama `session_regenerate_id(true)`, que troca o identificador da sessão para evitar *session fixation*;
4. guarda na sessão o objeto do usuário, o IP e o `User-Agent`.

### 4.3 O mecanismo de guardas
O controller base (`app/core/Controller.php`) tem quatro guardas, todas no início do método do controller:

| Guarda | Deixa passar |
|---|---|
| `autenticacaoRequired()` | qualquer usuário logado e ativo |
| `trabalhadorRequired()` | trabalhador ou admin |
| `contratanteRequired()` | contratante ou admin |
| `adminRequired()` | somente admin |

---

## 5. O modelo de dados

### 5.1 A história em uma frase
Um **usuário** (contratante) publica uma **vaga** de uma **categoria**; outros **usuários** (trabalhadores) se **candidatam** a ela; a plataforma modera por **denúncias**; e trabalhadores têm **habilidades**.

### 5.2 As entidades centrais
- **`usuario`**: a pessoa (ou empresa). Guarda identidade, papéis (flags), tipo de pessoa (PF/PJ) e se está ativa.
- **`vaga`**: o anúncio. Tem `data_servico` (quando acontece), `data_limite` (prazo para se candidatar, opcional), `status` (ATIVA/ENCERRADA), `is_user_active` (espelho do contratante), `visibilidade` (VISIVEL/OCULTA/REMOVIDA), e `excluida_em` (exclusão lógica).
- **`candidatura`**: a relação entre um trabalhador e uma vaga (renomeada de "interesse"). Tem um status próprio (PENDENTE/ACEITO) e garante uma candidatura por par (trabalhador, vaga) via UNIQUE.
- **`denuncia`**: o registro de uma queixa e da decisão da moderação sobre ela. Tem `acao_moderacao` (NENHUMA, BLOQUEIO, VAGA_OCULTA, VAGA_REMOVIDA), e campos de rastreabilidade (`id_admin`, `analisada_em`).

### 5.3 Decisões de modelagem que você precisa saber justificar

**(a) `data_servico` separada de `data_limite`.** São dois conceitos diferentes. `data_servico` é o **dia em que o trabalho acontece**. `data_limite` é o **prazo para se candidatar** (opcional, RN 06). Separar também permite filtrar a busca e permite regras como "só registrar não comparecimento depois da data do serviço".

**(b) `UNIQUE(id_vaga, id_trabalhador)` em `candidatura`.** Impede a candidatura duplicada **no próprio banco**. O service também confere antes, mas a garantia de verdade é a do banco.

**(c) Os estados de moderação da vaga:** Três eixos independentes:
- `status`: a **vontade do contratante** ou o limite atingido;
- `is_user_active`: o **dono está ativo?** (espelho do banco);
- `visibilidade`: a **decisão da moderação**.
- `excluida_em`: **exclusão lógica** pelo dono (RN 17).

A vaga só aparece na listagem se os três estiverem favoráveis. Separar os eixos evita que uma ação desfaça outra.

---

## 6. Os fluxos principais

### 6.1 Publicação de uma vaga (RN 04, RN 05, RN 17)
1. `contratanteRequired()` → autenticação, conta ativa e papel;
2. `ValidadorRegrasNegocio::validarCriadorVaga()` → contratante ativo;
3. Validações de campo (título, remuneração, data, duração obrigatória);
4. `VagaService::criar` → `VagaRepository::criar` → `INSERT`;
5. Trigger `BEFORE INSERT` define `is_user_active`.

### 6.2 Candidatura do trabalhador (RN 07, RN 15)
1. `trabalhadorRequired()` → autenticação, conta ativa e papel;
2. `ValidadorRegrasNegocio::validarCandidatura()` → vaga ativa, visível, dentro do prazo, limite não atingido, sem duplicidade;
3. `InteresseService::demonstrarInteresse` → verifica se já existe;
4. `CandidaturaRepository::criar` → `INSERT`;
5. Banco recusa a segunda tentativa via `UNIQUE`.

### 6.3 Aceite de candidato (RN 10, RN 13)
1. `contratanteRequired()` + `podeGerenciarVaga()` → só o dono da vaga;
2. `ValidadorRegrasNegocio::validarAceitacaoCandidato()` → limite não atingido;
3. `InteresseService::aceitarInteressado` → `UPDATE candidatura SET status = 'ACEITO'`;
4. Se com esse aceite o limite foi atingido, `VagaService::encerrar` → `UPDATE vaga SET status = 'ENCERRADA'` (RN 05).

### 6.4 Denúncia e moderação (RN 12, RN 13, RN 16)
1. `autenticacaoRequired()` → qualquer usuário;
2. `DenunciaController::denunciar` → valida motivo e descrição (obrigatórios);
3. `DenunciaService::criar` → `INSERT denuncia (status = 'PENDENTE')`;
4. Admin modera: `DenunciaController::moderar` → `DenunciaService` executa ação em transação;
5. Ações: **bloquear** (desativa conta), **ocultar/remover** (muda `visibilidade` da vaga), **analisar** (encerra sem sanção);
6. Registra `id_admin` e `analisada_em` na denúncia.

---

## 7. As regras de negócio no código

| Regra | Validação | Camada |
|---|---|---|
| RN 04 | Contratante ativo cria vaga | ValidadorRegrasNegocio + Controller |
| RN 05 | Encerramento condicionado ao limite | ValidadorRegrasNegocio |
| RN 06 | Busca com filtros (bairro, categoria, data, remuneração) | VagaRepository |
| RN 07 | Candidatura apenas para trabalhador | ValidadorRegrasNegocio |
| RN 08 | Contratante vê candidatos das próprias vagas | Controller + Repository |
| RN 09 | Telefone visível desde candidatura | Repository (coluna selecionada) |
| RN 10 | Limite de aceitos não pode ser excedido | ValidadorRegrasNegocio |
| RN 12 | Motivo e descrição obrigatórios em denúncias | DenunciaController |
| RN 13 | Moderação: bloqueio, ocultação, remoção | DenunciaService |
| RN 14 | Permissões por papel no servidor | Controller guardas |
| RN 15 | Uma candidatura por vaga | ValidadorRegrasNegocio + UNIQUE no banco |
| RN 16 | Não-comparecimento com descrição | DenunciaController |
| RN 17 | Edição com restrições (título/categoria) | ValidadorRegrasNegocio |

**O princípio.** Regra de negócio mora no **service** (e, quando é de integridade, também no banco) por três razões. Primeiro, o service é o **único caminho comum**. Segundo, controller e view mudam com a tela; a regra não deveria mudar. Terceiro, e mais importante: **o navegador é território do usuário**. Qualquer regra que exista só no HTML ou no JavaScript pode ser burlada. Por isso o código repete a verificação no servidor: o HTML só melhora a experiência, mas quem garante é o service.

---

## 8. Validador centralizado de regras (`ValidadorRegrasNegocio.php`)

Arquivo novo que concentra em um único place todas as validações de regras de negócio:

- `validarCriadorVaga(usuario)` - RN 04: contratante autenticado e ativo;
- `validarEncerramentoVaga(vaga)` - RN 05: limite de aceitos atingido;
- `validarCandidatura(usuario, vaga)` - RN 07 + 15: trabalhador, vaga ativa, prazo, sem duplicidade;
- `validarAceitacaoCandidato(vaga, idCandidato)` - RN 10: não exceder limite;
- `validarNovoLimite(novoLimite, aceitos)` - RN 10: edição não reduz abaixo de aceitos;
- `validarEdicaoVaga(vagaAntiga, novoTitulo, novaCategoria)` - RN 17: título e categoria travados com candidaturas.

**Benefício:** toda regra de negócio em um só arquivo, fácil de auditar e manter.

---

## 9. Estrutura do repositório

### 9.1 Setup e banco de dados
```bash
# Uma única linha instala o banco completo:
mysql -u freelaja -proot freelaja < app/database/scripts/script.sql

# Já inclui:
# - Schema completo (todas as tabelas)
# - Triggers (is_user_active)
# - Dados de teste
# - Admin seed (admin@freelaja.com / admin123)
```

### 9.2 Onde cada coisa vive
| Caminho | O que vive ali |
|---|---|
| `app/services/ValidadorRegrasNegocio.php` | **Validações centralizadas de RN** |
| `app/controllers/VagaController.php` | Fluxo de vaga (RN 04, 05, 17) |
| `app/controllers/InteresseController.php` | Fluxo de candidatura (RN 07, 15, 10) |
| `app/controllers/DenunciaController.php` | Fluxo de denúncia (RN 12, 16, 13) |
| `app/repositories/CandidaturaRepository.php` | SQL de candidatura (tabela renomeada de "interesse") |
| `app/repositories/VagaRepository.php` | SQL de vaga |
| `app/database/scripts/script.sql` | Schema completo + dados + triggers (ÚNICO arquivo SQL) |

### 9.3 Código morto ou sem rota
Cerca de 1,4 mil linhas do repositório não rodam:

| Arquivo | Situação |
|---|---|
| `app/controllers/CandidaturaController.php`, `app/services/CandidaturaService.php` | módulo antigo, substituído por `Interesse*`; nenhuma rota chama |
| `app/services/HumoristaService.php` | referencia classe inexistente; sobra de outro projeto |
| `app/views/usuarios/`, `app/views/interesse/confirmada.php` | telas antigas, sem rota |
| `VagaController::encerrar`, `VagaController::reabrir` | métodos completos, sem rota |

---

## 10. Limitações conhecidas

1. **Sem proteção CSRF.** Nenhum formulário POST tem token. *Correção:* token por sessão em cada formulário.

2. **Login diferencia "usuário inexistente" de "senha errada".** *Correção:* mensagem única.

3. **Dados gravados já escapados.** Alguns campos são gravados com `htmlspecialchars` antes de ir para o banco, o que é um defeito de exibição.

4. **Histórico do trabalhador faz N+1 (uma consulta por candidatura).** *Correção:* `JOIN` em bloco.

5. **Busca por texto usa `LIKE '%termo%'`, não usa índice.** *Correção:* índice `FULLTEXT`.

6. **Front-end depende de CDN em tempo de execução.** Tailwind e fontes do Google são carregados de fora.

7. **Sem testes automatizados** e cerca de 13% de código morto.

---

## 11. Perguntas prováveis da banca

**1. Por que existe um ValidadorRegrasNegocio separado?**  
Para centralizar todas as regras de negócio em um único lugar, fácil de auditar. Cada validação é um método que corresponde a uma RN. Evita código espalhado pelos services.

**2. A tabela `candidatura` é a mesma que era `interesse`?**  
Sim, foi renomeada. O schema usa `candidatura`, mas alguns arquivos ainda usam o nome da classe `Interesse` (`app/models/Interesse.php`). É uma limitação de nomenclatura, mas o funcionamento está correto. O banco é a fonte da verdade.

**3. Por que remover a tabela `advertencia`?**  
A especificação define que o sistema só tem bloqueio e moderação de vagas (ocultar/remover). Advertência não faz parte do escopo final. Bloqueio é mais drástico mas mais claro: impede o login.

**4. Como o sistema escalaria?**  
Hoje há limites: sem paginação de verdade, histórico com N+1. Para escalar: `JOIN`s no histórico, índice `FULLTEXT` na busca, paginação com offset limitado. A estrutura em camadas facilita porque o SQL está isolado nos repositories.

---

## 12. Glossário

- **Vaga / anúncio**: a oferta de serviço publicada pelo contratante. Tabela `vaga`.
- **Candidatura / interesse**: a manifestação de um trabalhador por uma vaga. Tabela `candidatura` (renomeada).
- **Aceito / selecionado**: candidato escolhido pelo contratante (`candidatura.status = 'ACEITO'`); só então o contato é liberado.
- **Contato**: e-mail e telefone do trabalhador; liberado apenas ao contratante da vaga e apenas após a seleção.
- **`data_servico`**: dia em que o serviço acontece (obrigatória, não pode estar no passado).
- **`data_limite`**: prazo opcional para se candidatar.
- **`status` da vaga**: `ATIVA` ou `ENCERRADA` (vontade do contratante ou limite atingido).
- **`is_user_active`**: espelho de `usuario.ativo` na vaga, mantido por trigger.
- **`visibilidade`**: decisão da moderação: `VISIVEL`, `OCULTA` ou `REMOVIDA`.
- **`excluida_em`**: exclusão lógica da vaga pelo dono (RN 17).
- **Denúncia**: queixa contra um usuário ou um anúncio. Tabela `denuncia`.
- **Moderação**: análise do administrador sobre uma denúncia, com uma ação (bloqueio, ocultação, remoção).
- **Soft delete**: tirar algo de circulação sem apagar o registro (ocultação/remoção).
- **Não comparecimento**: falta do trabalhador selecionado, registrada como denúncia (RN 16).

---

**Última atualização:** 2026-09-28  
**Autoria:** Claude Haiku 4.5
