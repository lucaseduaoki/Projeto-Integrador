# 🚀 FreelaJA - Projeto Integrador

O **FreelaJA** é uma plataforma de freelance desenvolvida em PHP com arquitetura MVC. No sistema, usuários podem criar anúncios de trabalho, realizar candidaturas, receber avaliações e gerenciar interações dentro da plataforma de forma simples.

---

# 🚀 Como Executar o Projeto

Para iniciar o projeto, basta subir os containers com Docker Compose:

```bash
docker-compose up --build -d
```

Após a inicialização:

- Aplicação: http://localhost:8080
- phpMyAdmin: http://localhost:8081
- MySQL: porta 3306

Containers do ambiente:

- `app` → aplicação PHP
- `mysql` → banco de dados
- `phpmyadmin` → gerenciamento visual do banco

O banco é inicializado automaticamente pelo script:

```text
app/database/scripts/script.sql
```

Com tudo em execução, abra no navegador:

- http://localhost:8080

### Executando no Windows

No Windows, o fluxo é praticamente o mesmo. Você só precisa garantir que o **Docker Desktop** esteja instalado e rodando (com WSL2 habilitado).

1. Abra o terminal (PowerShell, Windows Terminal ou terminal do VS Code) na pasta do projeto.
2. Rode o comando:

```bash
docker-compose up --build -d
```

Se não funcionar no seu ambiente, use:

```bash
docker compose up --build -d
```

Depois disso, acesse:

- Aplicação: http://localhost:8080
- phpMyAdmin: http://localhost:8081

Se houver conflito de porta, verifique se as portas **8080**, **8081** ou **3306** já estão em uso por outro serviço.

---

# 🔐 Usuários Padrão

O projeto já cria automaticamente 2 usuários de teste no banco:

- **Trabalhador**
	- Email: `trabalhador@gmail.com`
	- Senha: `trabalhador@gmail.com`
- **Contratante**
	- Email: `contratante@gmail.com`
	- Senha: `contratante@gmail.com`

---

# 🏗️ Arquitetura e Estrutura do Projeto

O projeto utiliza arquitetura **MVC em PHP puro**, separando responsabilidades para facilitar manutenção e evolução do código.

- **Controllers**: recebem as requisições
- **Services**: possuem regras de negócio
- **Repositories**: fazem acesso ao banco
- **Models**: representam entidades
- **Views**: renderizam as páginas
- **Core**: contém roteamento e classes base
- **Database**: configuração e scripts de banco

Estrutura simplificada:

```text
app/
├── config/
├── controllers/
├── core/
├── database/
├── helpers/
├── models/
├── repositories/
├── services/
└── views/
public/
docker-compose.yml
Dockerfile
README.md
```

---

# 🛠️ Tecnologias Utilizadas

- PHP 8
- MySQL 8
- Docker
- Docker Compose
- Composer
- Arquitetura MVC

---

# 📖 Documentação Adicional

- `DOCKER.md` → comandos e ambiente Docker
- `MANUAL.md` → manual e funcionamento do sistema

---

# 👥 Autores

- Equipe FreelaJA - Projeto Integrador

---

# 🖼️ Views a criar

Baseado nos `controllers` e `services` (desconsiderando as views já existentes), estas views precisam ser implementadas:

- autenticacao/login
- autenticacao/cadastro
- usuario/cadastro
- usuario/perfil
- anuncio/anuncio_list
- anuncio/anuncio_busca
- anuncio/anuncio_show
- anuncio/anuncio_form
- avaliacao/form
- avaliacao/sucesso
- candidatura/sucesso
- candidatura/erro
- candidatura/candidatos_list
- candidatura/confirmada
- candidatura/historico
- denuncia/denuncia_form
- denuncia/sucesso
- denuncia/listar

Crie os templates em `app/views/` respeitando essa estrutura de pastas.
