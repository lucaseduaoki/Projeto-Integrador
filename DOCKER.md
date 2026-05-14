# 🚀 FreelaJá - Docker Setup

## Requisitos
- Docker
- Docker Compose

## Como Executar

### 1. Construir e Iniciar os Containers
```bash
docker-compose up -d --build
```

### 2. Aguardar Inicialização
A aplicação iniciará em aproximadamente 30 segundos. O MySQL precisa tempo para:
- Inicializar
- Executar o script SQL
- Aceitar conexões

Você pode verificar o status com:
```bash
docker-compose logs -f mysql
```

### 3. Acessar a Aplicação
```
http://localhost:8080
```

### 4. Credenciais de Teste

**Banco de Dados:**
- Host: `localhost` (ou `mysql` dentro do Docker)
- Porta: `3306`
- Usuário: `freelaja_user`
- Senha: `freelaja_pass`
- Database: `freelaja`

**Usuário Admin (criar manualmente):**
```sql
INSERT INTO usuario (nome, email, senha, tipo_usuario, ativo, data_cadastro)
VALUES (
    'Admin',
    'admin@freelaja.com',
    '$2y$12$...',  -- bcrypt hash de 'password123'
    'ADMIN',
    1,
    NOW()
);
```

## Comandos Úteis

### Ver logs
```bash
docker-compose logs -f app
docker-compose logs -f mysql
```

### Acessar MySQL
```bash
docker-compose exec mysql mysql -u freelaja_user -p freelaja
# Senha: freelaja_pass
```

### Parar containers
```bash
docker-compose down
```

### Remover volumes (limpar banco)
```bash
docker-compose down -v
```

### Rebuild sem cache
```bash
docker-compose up -d --build --no-cache
```

## Solução de Problemas

### Porta 8080 já em uso
Mude no `docker-compose.yml`:
```yaml
ports:
  - "8081:80"  # Mude primeiro número
```

### Erro de conexão MySQL
```bash
docker-compose down -v
docker-compose up -d --build
```

### Ver IP do container
```bash
docker inspect app_freelaja | grep IPAddress
```

## Estrutura de Pastas Montadas

```
projeto_integrador/
├── app/                    ← Código da aplicação
├── public/                 ← Raiz web (index.php)
├── Dockerfile             ← Configuração PHP/Apache
└── docker-compose.yml     ← Orquestração
```

## Testar Rotas

1. **Home**
   ```
   http://localhost:8080/anuncios
   ```

2. **Login**
   ```
   http://localhost:8080/login
   ```

3. **Cadastro**
   ```
   http://localhost:8080/cadastro
   ```

## Verificar Saúde

### Status dos Containers
```bash
docker-compose ps
```

### Verificar Conectividade
```bash
docker-compose exec app php -r "echo 'PHP OK';"
docker-compose exec mysql mysqladmin ping
```

---

**Status**: ✅ Pronto para testar!
