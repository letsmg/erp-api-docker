# ERP API Docker Setup

Este documento descreve como configurar e executar o projeto ERP API modular usando containers Docker.

## Arquitetura

O projeto foi refatorado para funcionar com containers Docker separados:

- **API Gateway**: Container principal que orquestra as requisições
- **Módulos Independentes**: Cada módulo (Auth, Client, Product, User, Sale) roda em seu próprio container
- **Redis**: Para cache e sessões
- **MySQL**: Banco de dados (opcional, SQLite é o padrão)
- **Nginx Load Balancer**: Para balanceamento de carga em produção

## Estrutura de Containers

```
┌─────────────────┐    ┌─────────────────┐
│   API Gateway   │    │  Nginx LB (Prod)│
│     (8000)      │    │     (80/443)    │
└─────────┬───────┘    └─────────┬───────┘
          │                      │
          ├──────────────────────┤
          │                      │
    ┌─────▼─────┐    ┌──────────▼──────────┐
    │ Auth (9001)│    │   Client (9002)     │
    └───────────┘    └─────────────────────┘
    ┌─────▼─────┐    ┌──────────▼──────────┐
    │ User (9004)│    │   Product (9003)    │
    └───────────┘    └─────────────────────┘
    ┌─────▼─────┐    ┌──────────▼──────────┐
    │ Sale (9005)│    │   Redis (6379)      │
    └───────────┘    └─────────────────────┘
```

## Pré-requisitos

- Docker Desktop instalado
- Docker Compose
- Mínimo 4GB de RAM disponível

## Configuração Inicial

### 1. Preparar o Ambiente

```bash
# Copiar arquivo de ambiente Docker
cp .env.docker .env

# Gerar chave da aplicação
php artisan key:generate

# Criar banco de dados SQLite
touch database/database.sqlite
```

### 2. Construir e Iniciar Containers (Desenvolvimento)

```bash
# Construir todas as imagens
docker-compose build

# Iniciar todos os serviços
docker-compose up -d

# Verificar status dos containers
docker-compose ps
```

### 3. Acessar os Serviços

- **API Principal**: http://localhost:8000
- **Auth Service**: http://localhost:9001
- **Client Service**: http://localhost:9002
- **Product Service**: http://localhost:9003
- **User Service**: http://localhost:9004
- **Sale Service**: http://localhost:9005
- **Redis**: localhost:6379

## Endpoints da API

### Via API Gateway (Recomendado)

```
GET    http://localhost:8000/api/v1/auth/login
POST   http://localhost:8000/api/v1/auth/login
GET    http://localhost:8000/api/v1/clients
POST   http://localhost:8000/api/v1/clients
GET    http://localhost:8000/api/v1/products
POST   http://localhost:8000/api/v1/products
```

### Via Microservices (Acesso Direto)

```
POST   http://localhost:9001/auth/login
GET    http://localhost:9002/clients
POST   http://localhost:9002/clients
GET    http://localhost:9003/products
POST   http://localhost:9003/products
```

## Comandos Úteis

### Gerenciamento de Containers

```bash
# Iniciar serviços
docker-compose up -d

# Parar serviços
docker-compose down

# Reconstruir imagens
docker-compose build --no-cache

# Ver logs de um serviço específico
docker-compose logs -f api-gateway
docker-compose logs -f auth-service

# Executar comandos dentro de um container
docker-compose exec api-gateway php artisan migrate
docker-compose exec api-gateway php artisan tinker
```

### Desenvolvimento

```bash
# Iniciar apenas o API Gateway para desenvolvimento rápido
docker-compose up -d api-gateway redis

# Reconstruir apenas um módulo específico
docker-compose build auth-service
docker-compose up -d auth-service
```

### Banco de Dados

```bash
# Executar migrações
docker-compose exec api-gateway php artisan migrate

# Criar novo seeder
docker-compose exec api-gateway php artisan make:seed UserSeeder

# Executar seeders
docker-compose exec api-gateway php artisan db:seed
```

## Configuração de Produção

### 1. Configurar Ambiente de Produção

```bash
# Copiar configuração de produção
cp .env.docker .env.production

# Editar variáveis de produção
# - Alterar APP_ENV=production
# - Configurar APP_URL para seu domínio
# - Configurar credenciais do banco de dados
# - Configurar REDIS_HOST
```

### 2. Iniciar Serviços de Produção

```bash
# Usar perfil de produção
docker-compose --profile production up -d

# Ou usar arquivo específico
docker-compose -f docker-compose.prod.yml up -d
```

### 3. Configurações Adicionais de Produção

- Configurar SSL certificates em `docker/nginx-lb/ssl/`
- Ajustar configurações de MySQL em `docker/mysql/my.cnf`
- Configurar backup automático do banco de dados
- Configurar monitoramento e logs

## Volumes e Persistência

### Dados Persistentes

- **storage_data**: Arquivos de storage da API principal
- **mysql_data**: Dados do banco de dados MySQL
- **redis_data**: Dados do cache Redis
- **{module}_storage**: Storage específico de cada módulo

### Backup

```bash
# Backup do banco de dados
docker-compose exec mysql mysqldump -u root -p erp_api_prod > backup.sql

# Backup dos volumes
docker run --rm -v erp-api-docker_mysql_data:/data -v $(pwd):/backup alpine tar czf /backup/mysql_backup.tar.gz -C /data .
```

## Monitoramento e Logs

### Verificar Saúde dos Serviços

```bash
# Ver status de todos os containers
docker-compose ps

# Ver uso de recursos
docker stats

# Ver logs em tempo real
docker-compose logs -f --tail=100
```

### Health Checks

```bash
# Verificar se API está respondendo
curl http://localhost:8000/health

# Verificar se módulos estão ativos
curl http://localhost:9001/health
curl http://localhost:9002/health
```

## Troubleshooting

### Problemas Comuns

1. **Portas já em uso**
   ```bash
   # Verificar processos usando as portas
   netstat -tulpn | grep :8000
   
   # Mapear para outras portas em docker-compose.yml
   ports:
     - "8001:80"  # Mudar de 8000 para 8001
   ```

2. **Permissões de arquivos**
   ```bash
   # Ajustar permissões no host
   sudo chown -R $USER:$USER storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

3. **Conexão com banco de dados**
   ```bash
   # Verificar se MySQL está rodando
   docker-compose exec mysql mysql -u root -p
   
   # Verificar configuração de rede
   docker network ls
   docker network inspect erp-api-docker_erp-network
   ```

### Debug

```bash
# Entrar no container para debug
docker-compose exec api-gateway bash

# Ver configuração PHP
docker-compose exec api-gateway php -i | grep memory_limit

# Ver logs de erro do Nginx
docker-compose exec api-gateway tail -f /var/log/nginx/error.log
```

## Performance e Escalabilidade

### Escalar Horizontalmente

```bash
# Escalar um módulo específico
docker-compose up -d --scale client-service=3

# Escalar todos os serviços
docker-compose up -d --scale auth-service=2 --scale client-service=2 --scale product-service=2
```

### Otimizações

- Usar Redis para cache e sessões
- Configurar OPcache para PHP
- Habilitar gzip no Nginx
- Usar CDN para assets estáticos
- Configurar connection pooling para banco de dados

## Segurança

### Boas Práticas

- Não expor ports desnecessários
- Usar senhas fortes para banco de dados
- Configurar HTTPS em produção
- Limitar rate de requisições
- Usar variáveis de ambiente para secrets
- Atualizar imagens Docker regularmente

### Variáveis de Ambiente Sensíveis

```bash
# Nunca commitar estas variáveis
DB_PASSWORD=
REDIS_PASSWORD=
APP_KEY=
MAIL_PASSWORD=
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
```

## Manutenção

### Atualização

```bash
# Pull de novas imagens
docker-compose pull

# Reconstruir com atualizações
docker-compose build --no-cache

# Reiniciar com novas imagens
docker-compose up -d --force-recreate
```

### Limpeza

```bash
# Remover containers parados
docker container prune

# Remover imagens não usadas
docker image prune

# Limpar volumes não usados (cuidado!)
docker volume prune
```

## Suporte

Para dúvidas e problemas:

1. Verificar logs dos containers
2. Consultar a documentação oficial do Laravel
3. Verificar documentação do Docker
4. Abrir issue no repositório do projeto
