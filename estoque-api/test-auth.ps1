# Script de Teste - Sistema de Autenticação Sanctum
# ================================================

$ErrorActionPreference = 'Stop'
$base = 'http://127.0.0.1:8000/api'
$headers = @{ Accept = 'application/json' }

Write-Host "┌─────────────────────────────────────────┐" -ForegroundColor Cyan
Write-Host "│  TESTE - SISTEMA DE AUTENTICAÇÃO       │" -ForegroundColor Cyan
Write-Host "│  Estoque Pro - Laravel Sanctum         │" -ForegroundColor Cyan
Write-Host "└─────────────────────────────────────────┘" -ForegroundColor Cyan
Write-Host ""

# 1. TESTE DE LOGIN
Write-Host "[1] Testando login com credenciais..." -ForegroundColor Yellow

$loginPayload = @{
    email    = 'joao.silva@example.com'
    password = 'password'
} | ConvertTo-Json

try {
    $loginResponse = Invoke-RestMethod `
        -Uri "$base/login" `
        -Method Post `
        -ContentType 'application/json' `
        -Headers $headers `
        -Body $loginPayload

    $token = $loginResponse.token
    $userId = $loginResponse.user.id
    $userName = $loginResponse.user.name

    Write-Host "✅ Login bem-sucedido!" -ForegroundColor Green
    Write-Host "   Usuário: $userName" -ForegroundColor Gray
    Write-Host "   ID: $userId" -ForegroundColor Gray
    Write-Host "   Token: $($token.Substring(0, 20))..." -ForegroundColor Gray
    Write-Host ""
}
catch {
    Write-Host "❌ Erro no login: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# 2. TESTE DE ACESSO A PRODUTOS (PROTEGIDO)
Write-Host "[2] Testando acesso a produtos (protegido)..." -ForegroundColor Yellow

$authHeaders = @{
    Authorization = "Bearer $token"
    Accept        = 'application/json'
}

try {
    $productsResponse = Invoke-RestMethod `
        -Uri "$base/products" `
        -Method Get `
        -Headers $authHeaders

    $count = @($productsResponse.data).Count
    Write-Host "✅ Acesso autorizado aos produtos!" -ForegroundColor Green
    Write-Host "   Total de produtos: $count" -ForegroundColor Gray
    Write-Host ""
}
catch {
    Write-Host "❌ Erro ao acessar produtos: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# 3. TESTE DE ACESSO SEM TOKEN (DEVE FALHAR)
Write-Host "[3] Testando acesso sem token (deve falhar)..." -ForegroundColor Yellow

try {
    $noTokenResponse = Invoke-RestMethod `
        -Uri "$base/products" `
        -Method Get `
        -Headers $headers `
        -ErrorAction Stop

    Write-Host "❌ ERRO: A rota deveria estar protegida!" -ForegroundColor Red
    exit 1
}
catch {
    $statusCode = $_.Exception.Response.StatusCode.Value__
    if ($statusCode -eq 401) {
        Write-Host "✅ Acesso corretamente bloqueado!" -ForegroundColor Green
        Write-Host "   Status: 401 Unauthorized" -ForegroundColor Gray
        Write-Host ""
    }
    else {
        Write-Host "❌ Erro inesperado: Status $statusCode" -ForegroundColor Red
        exit 1
    }
}

# 4. TESTE DE LOGOUT
Write-Host "[4] Testando logout..." -ForegroundColor Yellow

try {
    $logoutResponse = Invoke-RestMethod `
        -Uri "$base/logout" `
        -Method Post `
        -Headers $authHeaders

    Write-Host "✅ Logout bem-sucedido!" -ForegroundColor Green
    Write-Host "   Mensagem: $($logoutResponse.message)" -ForegroundColor Gray
    Write-Host ""
}
catch {
    Write-Host "❌ Erro no logout: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# 5. TESTE DE ACESSO APÓS LOGOUT (DEVE FALHAR)
Write-Host "[5] Testando acesso após logout (deve falhar)..." -ForegroundColor Yellow

try {
    $afterLogoutResponse = Invoke-RestMethod `
        -Uri "$base/products" `
        -Method Get `
        -Headers $authHeaders `
        -ErrorAction Stop

    Write-Host "❌ ERRO: Usuário deveria estar desautorizado!" -ForegroundColor Red
    exit 1
}
catch {
    $statusCode = $_.Exception.Response.StatusCode.Value__
    if ($statusCode -eq 401) {
        Write-Host "✅ Token corretamente revogado!" -ForegroundColor Green
        Write-Host "   Status: 401 Unauthorized" -ForegroundColor Gray
        Write-Host ""
    }
    else {
        Write-Host "❌ Erro inesperado: Status $statusCode" -ForegroundColor Red
        exit 1
    }
}

# 6. TESTE COM CREDENCIAIS INVÁLIDAS
Write-Host "[6] Testando login com credenciais inválidas..." -ForegroundColor Yellow

$invalidLogin = @{
    email    = 'user@example.com'
    password = 'senhaerrada'
} | ConvertTo-Json

try {
    $invalidResponse = Invoke-RestMethod `
        -Uri "$base/login" `
        -Method Post `
        -ContentType 'application/json' `
        -Headers $headers `
        -Body $invalidLogin `
        -ErrorAction Stop

    Write-Host "❌ ERRO: Credenciais inválidas deveriam ser rejeitadas!" -ForegroundColor Red
    exit 1
}
catch {
    $statusCode = $_.Exception.Response.StatusCode.Value__
    if ($statusCode -eq 422) {
        Write-Host "✅ Credenciais inválidas corretamente rejeitadas!" -ForegroundColor Green
        Write-Host "   Status: 422 Unprocessable Entity" -ForegroundColor Gray
        Write-Host ""
    }
    else {
        Write-Host "❌ Erro inesperado: Status $statusCode" -ForegroundColor Red
        exit 1
    }
}

Write-Host "┌─────────────────────────────────────────┐" -ForegroundColor Green
Write-Host "│  ✅ TODOS OS TESTES PASSARAM!          │" -ForegroundColor Green
Write-Host "│  Sistema de autenticação funcional     │" -ForegroundColor Green
Write-Host "└─────────────────────────────────────────┘" -ForegroundColor Green
