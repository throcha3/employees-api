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

Obs¹: Parte-se do pressuposto  que o desenvolvedor já tenha o Docker instalado e configurado na máquina

Obs²: Dependendo da versão, pode ser necessário substituir o "docker-compose" por "docker compose" (remover o hífen)

### 1. Configurar o ambiente

```bash
# Copiar o arquivo de configuração
cp env.docker.example .env
```

### 2. Iniciar os serviços

```bash
# Iniciar os containers (será construído se for a primeira vez)
docker-compose up -d

# instalar dependencias
docker compose exec app composer install

#permissão nas pastas
docker compose exec app chown -R www-data:www-data storage bootstrap/cache


```

### 3. Inicializar o banco de dados

```bash
# Executar as migrações
docker-compose exec app php artisan migrate

# executar seeders
docker-compose exec app php artisan db:seed
```

### 4. Configurar Passport

```bash
# Gerar chaves
docker-compose exec app php artisan passport:keys

# Gerar Client
docker-compose exec app php artisan passport:client --personal
```

### 5. Acessar a aplicação

-   **Aplicação**: http://localhost:1010
-   **Banco de Dados**: localhost:1011
    -   Usuário: `convenia`
    -   Senha: `convenia123`
    -   Database: `convenia`
-   **Documentação**: http://localhost:1010/docs (Se der erro, tente o comando de gerar docs da seção comandos úteis)
-   **Postman collection da documentação**: Dentro do repositório como "postman.collection.json"
```bash
# Usuário padrão pra fazer login (gerado pelo seeder)

{
    "email": "user@user.com",
    "password": "password",
    "device_name": "cli"
}
```

## Comandos úteis

```bash
#gerar as chaves do passport
docker-compose exec app php artisan passport:client --personal

#rodar os testes
docker-compose exec app ./vendor/bin/phpunit

#formatar código
docker-compose exec app ./vendor/bin/php-cs-fixer fix .

#gerar docs
docker-compose exec app php artisan scribe:generate

# Ver logs dos containers
docker-compose logs -f

# Executar comandos Artisan
docker-compose exec app php artisan [comando]

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

