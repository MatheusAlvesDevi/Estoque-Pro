# Sistema de Autenticação Sanctum - Estoque Pro

## ✅ Implementação Concluída

O sistema de autenticação com Laravel Sanctum foi implementado com sucesso no back-end.

## 📝 Alterações Realizadas

### 1. **User Model** (`app/Models/User.php`)
- ✅ Adicionado trait `HasApiTokens` para suporte a tokens API via Sanctum

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    // ...
}
```

### 2. **AuthController** (`app/Http/Controllers/AuthController.php`)
Criado novo controlador com dois métodos principais:

#### **login(Request $request)**
- Valida email e senha
- Retorna um `plainTextToken` após autenticação bem-sucedida
- Dados do usuário autenticado incluídos na resposta

```php
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}

Resposta 200:
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  },
  "token": "1|abcdef..."
}
```

#### **logout(Request $request)**
- Revoga o token de acesso atual
- Requer autenticação prévia com `auth:sanctum`

```php
POST /api/logout
Authorization: Bearer {token}

Resposta 200:
{
  "message": "Logout realizado com sucesso."
}
```

### 3. **Rotas API** (`routes/api.php`)
- ✅ Adicionadas rotas de autenticação (sem proteção)
- ✅ Protegidas todas as rotas de produtos, entregas, saídas, fornecedores e usuários com `auth:sanctum`

```php
// Autenticação (público)
POST   /api/login   → login
POST   /api/logout  → logout (protegido)

// Endpoints protegidos (requerem token)
GET    /api/products
POST   /api/products
GET    /api/products/{id}
PUT    /api/products/{id}
DELETE /api/products/{id}

GET    /api/entryProduct
POST   /api/entryProduct
// ... (todos protegidos)

GET    /api/exitProduct
POST   /api/exitProduct
// ... (todos protegidos)

GET    /api/supply
POST   /api/supply
// ... (todos protegidos)

GET    /api/users
POST   /api/users
// ... (todos protegidos)
```

## 🧪 Testando a Autenticação

### 1. **Fazer Login**
```powershell
$login = @{
  email = "user@example.com"
  password = "password"
} | ConvertTo-Json

$response = Invoke-RestMethod `
  -Uri "http://127.0.0.1:8000/api/login" `
  -Method Post `
  -ContentType "application/json" `
  -Body $login

$token = $response.token
Write-Output $token  # Copiar este token
```

### 2. **Usar o Token para Acessar Endpoints Protegidos**
```powershell
$headers = @{
  Authorization = "Bearer $token"
  Accept = "application/json"
}

# Listar produtos
$products = Invoke-RestMethod `
  -Uri "http://127.0.0.1:8000/api/products" `
  -Method Get `
  -Headers $headers

$products | ConvertTo-Json
```

### 3. **Fazer Logout**
```powershell
$headers = @{
  Authorization = "Bearer $token"
  Accept = "application/json"
}

$logout = Invoke-RestMethod `
  -Uri "http://127.0.0.1:8000/api/logout" `
  -Method Post `
  -Headers $headers

Write-Output $logout.message  # "Logout realizado com sucesso."
```

## 🔒 Segurança Implementada

1. **Validação de Email e Senha**
   - Email requerido e validado como formato de email
   - Senha mínima de 6 caracteres
   - Hash verificado contra bancos de dados

2. **Tokens Únicos**
   - Cada login gera um novo token via Sanctum
   - Token pode ser revogado via logout
   - Token inclui informações do usuário

3. **Middleware de Proteção**
   - `auth:sanctum` protege todes os endpoints críticos
   - Requisições sem token retornam 401 Unauthorized
   - Rate limiting aplicado em todos os endpoints (`throttle:api`)

4. **Tratamento de Erros**
   - Credenciais incorretas retornam 422 com mensagem clara
   - Erros internos mascarados com UUID para rastreamento
   - Sem exposição de stack traces ao cliente

## 📱 Próximas Etapas - Front-end Vue.js 3

Para completar a implementação no Vue.js 3 (Composition API):

1. **Criar Composable `useAuth.js`**
   - Lógica de login/logout
   - Armazenamento seguro do token
   - Gerenciamento de estado do usuário

2. **Axios Interceptor**
   - Adicionar token automaticamente ao header `Authorization`
   - Renovar ou refrescar token se expirado

3. **Guards de Rota**
   - Proteger rotas que requerem autenticação
   - Redirecionar para login se não autenticado

4. **Exemplo de Uso no Componente**
   ```javascript
   const { login, logout, user, token } = useAuth()
   
   const handleLogin = async () => {
     await login(email.value, password.value)
     router.push('/dashboard')
   }
   ```

## ✨ Resumo

- ✅ AuthController com login e logout
- ✅ User model com HasApiTokens
- ✅ Todos endpoints protegidos com auth:sanctum
- ✅ Rate limiting ativo
- ✅ Tratamento seguro de erros
- ✅ Pronto para integração com front-end Vue.js
