# Script simples para testar login
$base = 'http://127.0.0.1:8000/api'
$headers = @{ 'Accept' = 'application/json' }

$credentials = @(
    @{ email = 'joao.silva@example.com'; password = 'password' },
    @{ email = 'maria.oliveira@example.com'; password = 'password' },
    @{ email = 'carlos.santos@example.com'; password = 'password' },
    @{ email = 'ana.costa@example.com'; password = 'password' },
    @{ email = 'pedro.lima@example.com'; password = 'password' }
)

foreach ($cred in $credentials) {
    try {
        $body = @{
            email = $cred.email
            password = $cred.password
        } | ConvertTo-Json

        $response = Invoke-RestMethod `
            -Uri "$base/login" `
            -Method Post `
            -ContentType 'application/json' `
            -Headers $headers `
            -Body $body `
            -ErrorAction Stop

        Write-Host "✅ Login bem-sucedido com: $($cred.email)" -ForegroundColor Green
        Write-Host "   Token: $($response.token.Substring(0, 20))..." -ForegroundColor Gray
        Write-Host "   Usuário: $($response.user.name)" -ForegroundColor Gray
        exit 0
    }
    catch {
        $status = $_.Exception.Response.StatusCode.Value__
        Write-Host "❌ $($cred.email): Status $status" -ForegroundColor Gray
    }
}

Write-Host ""
Write-Host "Nenhum usuário encontrado com as credenciais de teste." -ForegroundColor Red
Write-Host "Por favor, verifique se existe um usuário no banco de dados." -ForegroundColor Yellow
