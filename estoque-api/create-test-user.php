<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = User::where('email', 'test@example.com')->first();

if ($user) {
    echo "✅ Usuário já existe: " . $user->email . "\n";
} else {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);
    echo "✅ Usuário criado com sucesso!\n";
    echo "   Email: " . $user->email . "\n";
    echo "   Senha: password123\n";
}
