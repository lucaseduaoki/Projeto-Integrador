# FreelaJá

Plataforma web que conecta quem precisa de um serviço pontual (**contratante**) a quem presta esse serviço (**trabalhador**). O contratante publica uma vaga, os trabalhadores se candidatam e o contratante aceita quem quiser, até o limite de vagas. Os dados de contato só aparecem para as duas partes depois que o candidato é aceito. Um **administrador** modera a plataforma por meio de denúncias.

O FreelaJá não processa pagamentos: ele apenas aproxima as partes.

## Funcionalidades

- **Cadastro e login** com validação e máscara de CPF/CNPJ. Pessoa física (CPF) é sempre trabalhador e pessoa jurídica (CNPJ) é sempre contratante.
- **Perfil** com foto, bairro, telefone e habilidades, além de uma página de perfil pública.
- **Vagas**: criar, editar, encerrar e excluir; listagem com busca e filtros. O prazo da vaga é controlado automaticamente.
- **Candidaturas**: o trabalhador se candidata e pode cancelar a candidatura. O contratante vê os interessados e aceita até o limite da vaga. Depois de aceito, o contato fica liberado, inclusive pelo WhatsApp.
- **Histórico** de candidaturas do trabalhador.
- **Denúncias** de usuários e anúncios, além de registro de não comparecimento.
- **Moderação (admin)**: bloquear a conta, remover o anúncio (remoção lógica) ou arquivar a denúncia sem sanção. Nenhum dado é apagado.

## Tecnologias

- **PHP 8** puro, com arquitetura MVC própria (sem framework, sem Composer e sem ORM)
- **MySQL** acessado via PDO, sempre com consultas parametrizadas
- **Apache** com `mod_rewrite`
- HTML, CSS e JavaScript nas views

## Estrutura do projeto

```
.
├── app/
│   ├── config/        # Config.php: ambiente, sessão, URL base e credenciais do banco
│   ├── controllers/   # Recebem a requisição, checam login/permissão e validam a entrada
│   ├── core/          # Router, Autoload e Controller base
│   ├── database/      # Conexão PDO, inicializador e scripts/script.sql
│   ├── helpers/       # Validador, Formatador e validação de upload de imagem
│   ├── models/        # Usuario, Vaga, Candidatura, Denuncia, Habilidade
│   ├── repositories/  # Todo o SQL fica aqui
│   ├── services/      # Regras de negócio
│   └── views/         # Telas (autenticação, vagas, candidaturas, perfil, denúncias)
├── public/
│   ├── index.php      # Ponto de entrada único e definição das rotas
│   ├── img/           # Logo, favicon e mascote
│   └── uploads/       # Fotos de perfil enviadas pelos usuários
├── .htaccess          # Redireciona a raiz para public/
└── ESTUDO_CODIGO.md   # Guia detalhado do funcionamento do código
```

Fluxo de uma requisição: `public/index.php` → `Router` → **Controller** → **Service** → **Repository** → MySQL → **View**.

## Como rodar

### Requisitos

- PHP 8.0 ou superior, com as extensões `pdo_mysql` e `fileinfo`
- MySQL 5.7+ ou MariaDB
- Apache com `mod_rewrite` habilitado (XAMPP, WAMP, Laragon etc.). Também roda no servidor embutido do PHP.

### 1. Clonar o repositório

```bash
git clone https://github.com/lucaseduaoki/Projeto-Integrador.git
cd Projeto-Integrador
```

### 2. Configurar o banco

Edite `app/config/Config.php` e preencha os dados do seu MySQL:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'freelaja');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

Não é preciso criar as tabelas à mão. Na primeira requisição, se o banco não existir, o sistema cria o banco e executa `app/database/scripts/script.sql`, que gera as tabelas, os triggers e os dados de exemplo. Para isso, o usuário do MySQL precisa ter permissão de `CREATE DATABASE`. Se preferir, importe o script manualmente num banco vazio.

### 3. Iniciar o servidor

**Com Apache:** coloque a pasta do projeto no diretório do servidor (por exemplo, `htdocs/` no XAMPP) e acesse `http://localhost/Projeto-Integrador/`.

**Com o servidor embutido do PHP:**

```bash
php -S localhost:8080 -t public
```

Depois, acesse `http://localhost:8080`.

## Usuários de teste

Todos os usuários abaixo vêm do script de dados de exemplo e usam a senha **`senha123`**.

| Perfil       | E-mail                     |
|--------------|----------------------------|
| Administrador| `admin@freelaja.com`       |
| Trabalhador  | `trabalhador@freelaja.com` |
| Contratante  | `contratante@freelaja.com` |

Outros trabalhadores e contratantes de exemplo estão em `app/database/scripts/script.sql`.

## Principais regras de negócio

- Um trabalhador só pode se candidatar uma vez a cada vaga.
- Só é possível se candidatar a vagas ativas, visíveis e dentro do prazo, e enquanto houver vagas disponíveis.
- O contratante não pode aceitar mais trabalhadores do que o limite definido na vaga.
- Os dados de contato só ficam visíveis entre as partes depois que o candidato é aceito.
- Uma conta desativada ou bloqueada perde o acesso já na próxima requisição, mesmo que a pessoa esteja logada.

## Documentação

- [ESTUDO_CODIGO.md](ESTUDO_CODIGO.md): guia completo da arquitetura, das regras de negócio e do banco de dados
- [Documento do projeto (Google Docs)](https://docs.google.com/document/d/1EZoaofSEq-m8n8y9d9BQfBu18LypzEiDL5pxvcKtXmw/edit?usp=sharing)

## Autores

Projeto Integrador desenvolvido por Micael e Lucas Aoki.
