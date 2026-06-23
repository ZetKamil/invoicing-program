<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Fix Products
$products = DB::table('products')->where('name', 'LIKE', '%â€“%')->get();
foreach ($products as $p) {
    DB::table('products')->where('id', $p->id)->update([
        'name' => str_replace('â€“', '-', $p->name),
        'description' => str_replace('â€“', '-', $p->description)
    ]);
    echo "Fixed product {$p->id}\n";
}

// Fix Invoice Items
$items = DB::table('invoice_items')->where('description', 'LIKE', '%â€“%')->get();
foreach ($items as $item) {
    DB::table('invoice_items')->where('id', $item->id)->update([
        'description' => str_replace('â€“', '-', $item->description)
    ]);
    echo "Fixed invoice_item {$item->id}\n";
}

echo "Done fixing DB.\n";
