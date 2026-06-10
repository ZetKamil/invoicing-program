<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenant = App\Models\Tenant::first();
App\Models\User::firstOrCreate(
    ['email' => 'client@bekob.com'],
    [
        'tenant_id' => $tenant->id,
        'name' => 'John Doe Client',
        'password' => Illuminate\Support\Facades\Hash::make('password'),
        'role' => App\Enums\UserRole::DISPATCHER
    ]
);
echo "User created.\n";
