<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Attempting to load first client and relations...\n";
    $client = \App\Models\Client::first();
    if (!$client) {
        echo "No clients found in the database.\n";
        exit;
    }
    echo "Found Client ID: " . $client->id . "\n";
    $client->load([
        'assignedSales',
        'invoices.payments',
        'meetingLogs',
        'opportunities.product',
    ]);
    echo "Successfully loaded relations!\n";
} catch (\Throwable $e) {
    echo "ERROR DETECTED:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
