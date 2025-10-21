# API de colaboradores feito com Laravel

Este projeto inclui um ambiente Docker completo para desenvolvimento/testes em ambiente local.

## Estrutura do Ambiente

-   **Aplicação Laravel**: PHP 8.2 com FPM
-   **Web Server**: Nginx
-   **Queue container**: Nginx (clone do webserver, porém rodando as filas do Horizon)
-   **Cache server**: Redis
-   **Banco de Dados**: MySQL 8.0
-   **Portas**:
    -   Aplicação: `1010`
    -   MySQL: `1011`

## Como usar

### 1. Configurar o ambiente

```bash
# Copiar o arquivo de configuração
cp env.docker.example .env
```

### 2. Iniciar os serviços

```bash
# Iniciar os containers (será construído se for a primeira vez)
docker-compose up -d
```

### 3. Configurar o banco de dados

```bash
# Executar as migrações
docker-compose exec app php artisan migrate

# executar seeders
docker-compose exec app php artisan db:seed
```

### 4. Acessar a aplicação

-   **Aplicação**: http://localhost:1010
-   **Banco de Dados**: localhost:1011
    -   Usuário: `convenia`
    -   Senha: `convenia123`
    -   Database: `convenia`

## Comandos úteis

```bash
# Ver logs dos containers
docker-compose logs -f

# Executar comandos Artisan
docker-compose exec app php artisan [comando]

# Acessar o container da aplicação
docker-compose exec app bash

# Parar os serviços
docker-compose down

# Parar e remover volumes (CUIDADO: apaga dados do banco)
docker-compose down -v
```

## Estrutura de Arquivos Docker

```
├── docker-compose.yml          # Configuração dos serviços
├── Dockerfile                  # Imagem da aplicação Laravel
├── .dockerignore              # Arquivos ignorados no build
├── env.docker.example         # Exemplo de configuração
└── docker/
    └── nginx/
        └── default.conf       # Configuração do Nginx
```

