<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\Product;
use App\Models\EntryProduct;
use App\Models\ExitProduct;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuários
        User::create([
            'name' => 'João Silva',
            'email' => 'joao.silva@example.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Maria Oliveira',
            'email' => 'maria.oliveira@example.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Carlos Santos',
            'email' => 'carlos.santos@example.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Ana Costa',
            'email' => 'ana.costa@example.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Pedro Lima',
            'email' => 'pedro.lima@example.com',
            'password' => Hash::make('password'),
        ]);

        // Fornecedores
        $supplier1 = Supplier::create([
            'name' => 'Apple Inc.',
            'CNPJ' => '12345678000100',
            'tel' => '(11) 99999-0001',
            'email' => 'contato@apple.com',
        ]);

        $supplier2 = Supplier::create([
            'name' => 'Samsung Electronics',
            'CNPJ' => '98765432000199',
            'tel' => '(11) 99999-0002',
            'email' => 'contato@samsung.com',
        ]);

        $supplier3 = Supplier::create([
            'name' => 'Dell Technologies',
            'CNPJ' => '45678912000188',
            'tel' => '(11) 99999-0003',
            'email' => 'contato@dell.com',
        ]);

        $supplier4 = Supplier::create([
            'name' => 'HP Inc.',
            'CNPJ' => '11223344000177',
            'tel' => '(11) 99999-0004',
            'email' => 'contato@hp.com',
        ]);

        $supplier5 = Supplier::create([
            'name' => 'Lenovo Group',
            'CNPJ' => '55667788000166',
            'tel' => '(11) 99999-0005',
            'email' => 'contato@lenovo.com',
        ]);

        // Produtos
        $product1 = Product::create([
            'name' => 'iPhone 15 Pro',
            'code' => 'IPH15P-128',
            'price' => 8999.99,
            'quantity' => 50,
            'minimumstock' => 10,
            'supplier_id' => $supplier1->id,
        ]);

        $product2 = Product::create([
            'name' => 'Samsung Galaxy S23',
            'code' => 'SGS23-256',
            'price' => 4999.99,
            'quantity' => 30,
            'minimumstock' => 5,
            'supplier_id' => $supplier2->id,
        ]);

        $product3 = Product::create([
            'name' => 'Dell Inspiron 15',
            'code' => 'DIN15-8GB',
            'price' => 3499.99,
            'quantity' => 20,
            'minimumstock' => 3,
            'supplier_id' => $supplier3->id,
        ]);

        $product4 = Product::create([
            'name' => 'HP Pavilion 14',
            'code' => 'HPP14-16GB',
            'price' => 4299.99,
            'quantity' => 25,
            'minimumstock' => 4,
            'supplier_id' => $supplier4->id,
        ]);

        $product5 = Product::create([
            'name' => 'Lenovo ThinkPad X1',
            'code' => 'LTX1-512',
            'price' => 12999.99,
            'quantity' => 15,
            'minimumstock' => 2,
            'supplier_id' => $supplier5->id,
        ]);

        $product6 = Product::create([
            'name' => 'iPad Pro 12.9"',
            'code' => 'IPD12-256',
            'price' => 10999.99,
            'quantity' => 40,
            'minimumstock' => 8,
            'supplier_id' => $supplier1->id,
        ]);

        $product7 = Product::create([
            'name' => 'Samsung Galaxy Tab S8',
            'code' => 'SGTS8-128',
            'price' => 3999.99,
            'quantity' => 35,
            'minimumstock' => 6,
            'supplier_id' => $supplier2->id,
        ]);

        // Entradas
        EntryProduct::create([
            'name' => 'Entrada de iPhones',
            'data_de_entrada' => '2026-03-10',
            'quantity' => 20,
            'reason' => 'Compra de lote inicial',
            'product_id' => $product1->id,
            'user_id' => 1,
        ]);

        EntryProduct::create([
            'name' => 'Entrada de Galaxys',
            'data_de_entrada' => '2026-03-12',
            'quantity' => 15,
            'reason' => 'Reabastecimento',
            'product_id' => $product2->id,
            'user_id' => 2,
        ]);

        EntryProduct::create([
            'name' => 'Entrada de Dell',
            'data_de_entrada' => '2026-03-11',
            'quantity' => 10,
            'reason' => 'Novo fornecedor',
            'product_id' => $product3->id,
            'user_id' => 3,
        ]);

        EntryProduct::create([
            'name' => 'Entrada de HP',
            'data_de_entrada' => '2026-03-13',
            'quantity' => 12,
            'reason' => 'Promoção especial',
            'product_id' => $product4->id,
            'user_id' => 4,
        ]);

        EntryProduct::create([
            'name' => 'Entrada de Lenovo',
            'data_de_entrada' => '2026-03-14',
            'quantity' => 8,
            'reason' => 'Lote corporativo',
            'product_id' => $product5->id,
            'user_id' => 5,
        ]);

        // Saídas
        ExitProduct::create([
            'name' => 'Saída de iPhones',
            'data_de_saida' => '2026-03-14',
            'quantity' => 5,
            'reason' => 'Venda para cliente',
            'product_id' => $product1->id,
            'user_id' => 1,
        ]);

        ExitProduct::create([
            'name' => 'Saída de Dell',
            'data_de_saida' => '2026-03-13',
            'quantity' => 2,
            'reason' => 'Devolução',
            'product_id' => $product3->id,
            'user_id' => 3,
        ]);

        ExitProduct::create([
            'name' => 'Saída de Samsung Tab',
            'data_de_saida' => '2026-03-15',
            'quantity' => 3,
            'reason' => 'Venda online',
            'product_id' => $product7->id,
            'user_id' => 2,
        ]);

        ExitProduct::create([
            'name' => 'Saída de iPad',
            'data_de_saida' => '2026-03-12',
            'quantity' => 4,
            'reason' => 'Cliente corporativo',
            'product_id' => $product6->id,
            'user_id' => 1,
        ]);
    }
}
