<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Listing Clients and their assigned sales:\n";
foreach (\App\Models\Client::with('assignedSales')->get() as $c) {
    echo "Client ID: {$c->id} | Name: {$c->company_name} | Assigned Sales: " . ($c->assignedSales->name ?? 'None') . " (ID: {$c->assigned_sales_id})\n";
}

echo "\nListing Opportunities and their sales:\n";
foreach (\App\Models\Opportunity::with(['client', 'sales'])->get() as $o) {
    echo "Opp ID: {$o->id} | Title: {$o->title} | Client: " . ($o->client->company_name ?? 'None') . " (ID: {$o->client_id}) | Sales: " . ($o->sales->name ?? 'None') . " (ID: {$o->sales_id})\n";
}
