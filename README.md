# E-commerce API — Laravel + Sanctum

API REST para um e-commerce completo: produtos, categorias, carrinho, encomendas, pagamentos e avaliações. Autenticação via Laravel Sanctum (tokens), pensada para ser consumida por um front-end Angular separado.

## Stack

- **Laravel** (PHP 8.4)
- **MySQL**
- **Laravel Sanctum** — autenticação por token para SPA/API

---

## Instalação

### 1. Requisitos
- PHP 8.2+
- Composer
- MySQL a correr localmente

### 2. Clonar/copiar o projeto e instalar dependências
```bash
composer install
```

### 3. Configurar o `.env`
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_api
DB_USERNAME=root
DB_PASSWORD=
```
Cria a base de dados vazia antes de migrar:
```sql
CREATE DATABASE ecommerce_api;
```

### 4. Instalar o Sanctum (se ainda não estiver feito)
```bash
composer require laravel/sanctum
php artisan install:api
```
Confirma que `bootstrap/app.php` tem a linha `api: __DIR__.'/../routes/api.php'` — sem ela, todas as rotas `/api/*` dão 404.

### 5. Migrations
```bash
php artisan migrate
```

### 6. Configurar CORS (para o Angular)
Em `config/cors.php`:
```php
'paths' => ['api/*'],
'allowed_origins' => ['http://localhost:4200'],
'supports_credentials' => true,
```

### 7. Criar um utilizador admin
```bash
php artisan tinker
```
```php
App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@loja.com',
    'password' => bcrypt('password'), // a password de login é "password", não a hash
    'role' => 'admin',
]);
```

### 8. Arrancar o servidor
```bash
php artisan serve
```
API disponível em `http://127.0.0.1:8000`. **Todas as rotas abaixo levam o prefixo `/api`.**

---

## Autenticação

Depois de `POST /api/login` ou `/api/registo`, a resposta inclui um `token`. Envia-o em todos os pedidos protegidos:
```
Authorization: Bearer {token}
```

---

## Entidades e relacionamentos

| Entidade | Relação |
|---|---|
| **User** | 1:N CarrinhoItem, 1:N Encomenda, 1:N Avaliacao |
| **Categoria** | 1:N Produto |
| **Produto** | N:1 Categoria · 1:N Avaliacao · 1:N CarrinhoItem · 1:N EncomendaItem |
| **CarrinhoItem** | N:1 User · N:1 Produto |
| **Encomenda** | N:1 User · 1:N EncomendaItem · 1:1 Pagamento |
| **EncomendaItem** | N:1 Encomenda · N:1 Produto (guarda `preco_unitario` congelado no momento da compra) |
| **Pagamento** | 1:1 Encomenda |
| **Avaliacao** | N:1 Produto · N:1 User |

---

## Endpoints

### Autenticação (público)
| Método | Rota | Descrição |
|---|---|---|
| POST | `/api/registo` | Regista novo cliente |
| POST | `/api/login` | Login, devolve token |
| GET | `/api/me` 🔒 | Dados do utilizador autenticado |
| POST | `/api/logout` 🔒 | Termina a sessão (revoga o token) |

### Categorias
| Método | Rota | Descrição |
|---|---|---|
| GET | `/api/categorias` | Lista todas |
| GET | `/api/categorias/{id}` | Detalhe |
| POST | `/api/categorias` 🔒👑 | Criar |
| PUT | `/api/categorias/{id}` 🔒👑 | Atualizar |
| DELETE | `/api/categorias/{id}` 🔒👑 | Apagar |

### Produtos
| Método | Rota | Descrição |
|---|---|---|
| GET | `/api/produtos` | Lista pública, paginada. Filtros: `?categoria=slug&busca=texto&ordenar=preco_asc\|preco_desc\|recentes` |
| GET | `/api/produtos/{id}` | Detalhe |
| POST | `/api/produtos` 🔒👑 | Criar |
| PUT/PATCH | `/api/produtos/{id}` 🔒👑 | Atualizar |
| DELETE | `/api/produtos/{id}` 🔒👑 | Apagar |

### Carrinho (por utilizador autenticado)
| Método | Rota | Descrição |
|---|---|---|
| GET | `/api/carrinho` 🔒 | Ver carrinho + total |
| POST | `/api/carrinho` 🔒 | Adicionar produto (`produto_id`, `quantidade`) |
| PATCH | `/api/carrinho/{item}` 🔒 | Atualizar quantidade |
| DELETE | `/api/carrinho/{item}` 🔒 | Remover item |

### Encomendas
| Método | Rota | Descrição |
|---|---|---|
| GET | `/api/encomendas` 🔒 | Histórico do utilizador |
| GET | `/api/encomendas/{id}` 🔒 | Detalhe (dono ou admin) |
| POST | `/api/encomendas` 🔒 | Checkout — transforma o carrinho em encomenda (`endereco_entrega`, `metodo_pagamento`) |
| PATCH | `/api/encomendas/{id}/estado` 🔒👑 | Atualizar estado (pendente/paga/enviada/entregue/cancelada) |

### Avaliações
| Método | Rota | Descrição |
|---|---|---|
| GET | `/api/produtos/{id}/avaliacoes` | Listar avaliações de um produto |
| POST | `/api/produtos/{id}/avaliacoes` 🔒 | Criar (`nota` 1–5, `comentario`) — uma por utilizador/produto |
| DELETE | `/api/produtos/{id}/avaliacoes/{avaliacaoId}` 🔒 | Apagar (dono ou admin) |

🔒 = requer token · 👑 = requer utilizador com `role = admin`

---

## Regras de negócio importantes

- **Checkout transacional**: `POST /api/encomendas` corre dentro de uma transação de base de dados — se faltar stock de qualquer item, nada é gravado.
- **Preço congelado**: o preço de cada produto é copiado para `encomenda_itens.preco_unitario` no momento da compra, para nunca depender do preço atual (que pode mudar depois).
- **Stock**: verificado tanto ao adicionar ao carrinho como no checkout; é decrementado apenas na criação da encomenda.
- **Uma avaliação por utilizador/produto**, garantido por constraint única na base de dados.

---

## Problemas conhecidos / notas de depuração

- Se `php artisan migrate` ou as rotas `/api/*` derem 404 em massa, confirma que `bootstrap/app.php` regista `routes/api.php` (corrige com `php artisan install:api`).
- Depois de copiar/editar ficheiros manualmente, corre `composer dump-autoload` se aparecer erro `Class "..." does not exist`.
- Nomes de tabela em português (ex: `avaliacoes`) não seguem a pluralização automática do Eloquent (que assumiria `avaliacaos`) — por isso o model `Avaliacao` define `protected $table = 'avaliacoes';` explicitamente. Se criares mais models com nomes em português, confirma sempre se precisas de fazer o mesmo.
- Login espera a password em **texto simples** (ex: `password`), nunca a hash bcrypt guardada na base de dados.

---

## Próximos passos

Front-end Angular a consumir esta API: `AuthService`, `HttpInterceptor` para anexar o token automaticamente, `Guards` para rotas privadas, e componentes de listagem/carrinho/checkout.
