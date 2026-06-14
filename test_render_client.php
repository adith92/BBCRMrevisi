<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Rendering clients.show view...\n";
    $client = \App\Models\Client::first();
    if (!$client) {
        echo "No clients found.\n";
        exit;
    }
    
    // Set authenticated user to GM or Sales to avoid auth aborts
    $user = \App\Models\User::first();
    auth()->login($user);
    
    // Simulate ClientController@show stats calculation
    $client->load([
        'assignedSales',
        'invoices.payments',
        'meetingLogs',
        'opportunities' => function ($query) use ($user) {
            if ($user->isSales()) {
                $query->where('sales_id', $user->id);
            }
        },
        'opportunities.product',
    ]);
    
    $stats = [
        'total_spend'   => $client->invoices->where('status', 'paid')->sum('amount'),
        'total_pending' => $client->invoices->whereIn('status', ['sent', 'draft'])->sum('amount'),
        'total_overdue' => $client->invoices->where('status', 'overdue')->sum('amount'),
        'won_deals_count' => $client->opportunities->where('stage', 'won')->count(),
        'won_deals_sum'   => $client->opportunities->where('stage', 'won')->sum('final_value'),
    ];
    
    $html = view('clients.show', compact('client', 'stats'))->render();
    echo "Successfully rendered client show view! Length: " . strlen($html) . "\n";
} catch (\Throwable $e) {
    echo "ERROR DETECTED:\n";
    echo $e->getMessage() . "\n";
    echo "In file: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
