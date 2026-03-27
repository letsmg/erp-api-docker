# Quick Start Guide - MongoDB + Redis ERP API

## Setup Rápido (5 minutos)

### 1. Preparar Ambiente
```bash
# Copiar configuração Docker
cp .env.docker .env

# Instalar dependências MongoDB
composer update
```

### 2. Iniciar Containers
```bash
# Construir e iniciar todos os serviços
docker-compose build
docker-compose up -d

# Verificar status
docker-compose ps
```

### 3. Verificar Conexões
```bash
# Testar MongoDB
docker-compose exec mongodb mongosh -u root -p rootpassword --eval "db.adminCommand('ping')"

# Testar Redis
docker-compose exec redis redis-cli ping

# Testar API
curl http://localhost:8000/health
```

### 4. Rodar Testes de Performance
```bash
# Executar script de testes
docker-compose exec api-gateway php test_mongodb_redis.php

# Ou localmente (se tiver PHP+MongoDB instalados)
php test_mongodb_redis.php
```

## Endpoints Disponíveis

### API Gateway
- **API Base**: http://localhost:8000/api/v1
- **Health Check**: http://localhost:8000/health

### Módulos Independentes
- **Auth**: http://localhost:9001
- **Client**: http://localhost:9002  
- **Product**: http://localhost:9003
- **User**: http://localhost:9004
- **Sale**: http://localhost:9005

### Bancos de Dados
- **MongoDB**: localhost:27017 (user: root, pass: rootpassword)
- **Redis**: localhost:6379

## Exemplos de Uso

### 1. Busca Rápida (< 4 caracteres)
```bash
# Busca por código de barras
curl "http://localhost:8000/api/v1/products/search?term=123"

# Busca por modelo curto  
curl "http://localhost:8000/api/v1/products/search?term=ABC"
```

### 2. Cache em Ação
```bash
# Primeira requisição (cache miss)
time curl "http://localhost:8000/api/v1/products/1"

# Segunda requisição (cache hit)
time curl "http://localhost:8000/api/v1/products/1"
```

### 3. Operações MongoDB
```bash
# Conectar ao MongoDB
docker-compose exec mongodb mongosh -u root -p rootpassword

# Usar database
use erp_api

# Verificar produtos
db.products.find().limit(5)

# Verificar índices
db.products.getIndexes()
```

### 4. Operações Redis
```bash
# Conectar ao Redis
docker-compose exec redis redis-cli

# Verificar chaves de cache
KEYS product:*

# Verificar info
INFO memory
```

## Performance esperada

| Operação | Tempo | Descrição |
|----------|-------|-----------|
| Barcode search | ~1ms | Index direto |
| Short query (<4) | ~2ms | Regex otimizado |
| Cache hit | ~0.1ms | Redis memory |
| Cache miss | ~10ms | MongoDB + cache |

## Troubleshooting Rápido

### Problemas Comuns

1. **Portas ocupadas**
   ```bash
   # Mudar portas no docker-compose.yml
   ports:
     - "8001:80"  # API Gateway
     - "27018:27017"  # MongoDB
   ```

2. **Permissões**
   ```bash
   # Ajustar permissões no Linux/Mac
   sudo chown -R $USER:$USER storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

3. **MongoDB não inicia**
   ```bash
   # Verificar logs
   docker-compose logs mongodb
   
   # Limpar e reiniciar
   docker-compose down -v
   docker-compose up -d mongodb
   ```

4. **Cache não funciona**
   ```bash
   # Verificar configuração Redis
   docker-compose exec redis redis-cli ping
   
   # Limpar cache
   docker-compose exec api-gateway php artisan cache:clear
   ```

## Monitoramento Básico

### 1. Status dos Containers
```bash
# Ver todos os serviços
docker-compose ps

# Ver uso de recursos
docker stats
```

### 2. Logs em Tempo Real
```bash
# API Gateway
docker-compose logs -f api-gateway

# MongoDB
docker-compose logs -f mongodb

# Redis
docker-compose logs -f redis
```

### 3. Performance Queries
```bash
# Analisar query MongoDB
docker-compose exec mongodb mongosh --eval "
db.products.find({'barcode': '1234567890123'}).explain('executionStats')
"
```

## Próximos Passos

1. **Desenvolvimento**: Use `make dev` para reiniciar rápido
2. **Produção**: Configure SSL e variáveis de ambiente
3. **Monitoramento**: Implemente Prometheus + Grafana
4. **Backup**: Configure backups automáticos do MongoDB

## Comandos Úteis

```bash
# Reconstruir tudo
make rebuild

# Ver health check
make health

# Backup do MongoDB
make backup

# Limpeza
make clean
```

## Suporte

- 📖 Documentação completa: `DOCKER_README.md`
- 🗄️ MongoDB + Redis guide: `MONGODB_REDIS_README.md`
- 🧪 Testes: `php test_mongodb_redis.php`
- 🐛 Issues: Verificar logs dos containers
