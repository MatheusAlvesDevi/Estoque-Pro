# Documentação do Projeto - Estoque Pro

## 1. Visão Geral
O Estoque Pro é uma solução de gestão de estoque composta por:

- Backend API em Laravel 12 (PHP 8.2+), com autenticação via Sanctum.
- Frontend principal em Vue 3 + Vite + Pinia + Vue Router.
- Estrutura adicional em Next.js no diretório frontend estoque para evolução de interface e componentes.

Estrutura de alto nível:

- estoque-api: API REST, regras de negócio, autenticação e persistência.
- frontend estoque: aplicações/frontends e recursos visuais.

## 2. Stack Tecnológica
### Backend (estoque-api)
- PHP 8.2+
- Laravel 12
- Laravel Sanctum 4
- Banco de dados relacional via migrations do Laravel

### Frontend Vue (frontend estoque/src)
- Vue 3
- Vite
- Pinia
- Vue Router
- Axios
- Tailwind CSS

### Frontend Next.js (frontend estoque/app)
- Next.js
- Tailwind CSS

## 3. Pré-requisitos
Antes de iniciar, garanta que sua máquina tenha:

- PHP 8.2+
- Composer
- Node.js 18+
- npm
- Banco de dados configurado no arquivo .env da API

## 4. Configuração e Execução
## 4.1 Backend (Laravel API)
No diretório estoque-api:

1. Instalar dependências PHP:
   - composer install
2. Criar arquivo de ambiente:
   - copiar .env.example para .env (se ainda não existir)
3. Gerar chave da aplicação:
   - php artisan key:generate
4. Executar migrations:
   - php artisan migrate
5. Instalar dependências front do Laravel (se necessário para assets):
   - npm install
6. Subir API:
   - php artisan serve

Atalho opcional de setup (composer scripts):

- composer run setup

Modo desenvolvimento com serviços concorrentes (server, queue, logs e vite):

- composer run dev

## 4.2 Frontend Vue/Next (frontend estoque)
No diretório frontend estoque:

1. Instalar dependências:
   - npm install
2. Rodar em desenvolvimento:
   - npm run dev
3. Gerar build:
   - npm run build

Observação: o package.json deste diretório está configurado para Vite. A estrutura também contém arquivos de Next.js (como next.config.mjs), indicando coexistência/evolução de abordagens frontend.

## 5. Autenticação
O backend usa Laravel Sanctum com token Bearer.

Fluxo:

1. Cliente envia email e senha para POST /api/login.
2. API retorna token de acesso e dados do usuário.
3. Cliente envia Authorization: Bearer {token} nos endpoints protegidos.
4. POST /api/logout revoga o token atual.

## 6. Endpoints Principais
Base: /api

### Públicos
- POST /login

### Protegidos (auth:sanctum)
- POST /logout
- GET /user

### Recursos
- Products:
  - GET /products
  - POST /products
  - GET /products/{id}
  - PUT /products/{id}
  - DELETE /products/{id}
- EntryProduct:
  - GET /entryProduct
  - POST /entryProduct
  - GET /entryProduct/{id}
  - PUT /entryProduct/{id}
  - DELETE /entryProduct/{id}
- ExitProduct:
  - GET /exitProduct
  - POST /exitProduct
  - GET /exitProduct/{id}
  - PUT /exitProduct/{id}
  - DELETE /exitProduct/{id}
- Supply:
  - GET /supply
  - POST /supply
  - GET /supply/{id}
  - PUT /supply/{id}
  - DELETE /supply/{id}
- Users:
  - GET /users
  - POST /users
  - GET /users/{id}
  - PUT /users/{id}
  - DELETE /users/{id}

## 7. Organização de Pastas
### API (estoque-api)
- app/Http/Controllers: controladores HTTP
- app/Services: regras de negócio
- app/Repository: acesso a dados
- app/Models: entidades do domínio
- routes/api.php: rotas da API
- database/migrations: versionamento de schema

### Frontend (frontend estoque)
- src/: app Vue (componentes, views, stores, router, services)
- app/: estrutura Next.js
- components/: componentes reutilizáveis
- styles/ e app/globals.css: estilos globais

## 8. Testes e Qualidade
### Backend
- Executar suíte Laravel:
  - php artisan test
- Script Composer:
  - composer run test

Arquivos utilitários de teste de autenticação também estão presentes no backend, como:

- test-auth.ps1
- test-login-simple.ps1

## 9. Boas Práticas de Integração Frontend com API
- Centralizar cliente HTTP (Axios) em um único módulo.
- Incluir interceptor para anexar token Bearer automaticamente.
- Tratar 401 no interceptor para limpar sessão local e redirecionar para login.
- Evitar exibir erros técnicos completos ao usuário final; use mensagens amigáveis.
- Proteger rotas privadas no frontend com guardas de navegação.

## 10. Troubleshooting Rápido
- Erro de autenticação 401:
  - Verifique se o token Bearer está sendo enviado no header Authorization.
- Erro de conexão com API:
  - Confirme se o backend está ativo em php artisan serve e URL correta no frontend.
- Erro de migração:
  - Revise credenciais do banco no arquivo .env e rode php artisan migrate novamente.
- Configuração inconsistente:
  - Rode php artisan config:clear e php artisan cache:clear.

## 11. Documentos Relacionados
No backend existem documentos específicos úteis para aprofundamento:

- AUTHENTICATION.md
- README_AUTHENTICATION.md
- IMPLEMENTATION_SUMMARY.md

---

Documento criado para centralizar o setup e a operação do projeto em um único ponto de consulta.