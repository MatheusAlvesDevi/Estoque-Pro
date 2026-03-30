# Script para debugar erro 422 no login
$base = 'http://127.0.0.1:8000/api'
$headers = @{ 
    'Accept' = 'application/json'
    'Content-Type' = 'application/json'
}

Write-Host "🔍 Testando Login - Debug 422" -ForegroundColor Cyan
Write-Host ""

# Teste 1: Email e Senha Corretos
Write-Host "[Teste 1] Email e Senha Corretos" -ForegroundColor Yellow
$body = ConvertTo-Json @{ 
    email = 'joao.silva@example.com'
    password = 'password'
}

try {
    $response = Invoke-RestMethod -Uri "$base/login" -Method Post -Headers $headers -Body $body -ErrorAction Stop
    Write-Host "✅ LOGIN FUNCIONANDO!" -ForegroundColor Green
    Write-Host "Token: $($response.token)" -ForegroundColor Gray
    Write-Host "Usuário: $($response.user.name)" -ForegroundColor Gray
} catch {
    Write-Host "❌ ERRO $($_.Exception.Response.StatusCode.Value__)" -ForegroundColor Red
    
    # Tentar ler resposta de erro
    try {
        $errorResponse = $_.Exception.Response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($errorResponse)
        $errorBody = $reader.ReadToEnd()
        Write-Host "📋 Resposta do Servidor:" -ForegroundColor Yellow
        Write-Host $errorBody | ConvertFrom-Json | ConvertTo-Json -Depth 10 | Write-Host
    } catch {
        Write-Host "Não consegui ler a resposta de erro" -ForegroundColor Gray
    }
}

Write-Host ""
Write-Host "[Teste 2] Apenas Email (sem Senha)" -ForegroundColor Yellow
$body2 = ConvertTo-Json @{ email = 'joao.silva@example.com' }

try {
    Invoke-RestMethod -Uri "$base/login" -Method Post -Headers $headers -Body $body2 -ErrorAction Stop
} catch {
    Write-Host "Status: $($_.Exception.Response.StatusCode.Value__)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "[Teste 3] Apenas Senha (sem Email)" -ForegroundColor Yellow  
$body3 = ConvertTo-Json @{ password = 'password' }

try {
    Invoke-RestMethod -Uri "$base/login" -Method Post -Headers $headers -Body $body3 -ErrorAction Stop
} catch {
    Write-Host "Status: $($_.Exception.Response.StatusCode.Value__)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "[Teste 4] Corpo Vazio" -ForegroundColor Yellow

try {
    Invoke-RestMethod -Uri "$base/login" -Method Post -Headers $headers -Body '{}' -ErrorAction Stop
} catch {
    Write-Host "Status: $($_.Exception.Response.StatusCode.Value__)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "✅ Debug Completo" -ForegroundColor Green
