<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Simulating ActivityLogController@create...\n";
    $user = \App\Models\User::where('role', 'sales')->first();
    auth()->login($user);
    echo "Logged in as sales user: " . $user->name . " (ID: " . $user->id . ")\n";

    $opportunity = \App\Models\Opportunity::first();
    echo "Simulating for opportunity ID: " . $opportunity->id . "\n";

    $clients = \App\Models\Client::orderBy('company_name')->get();
    $opportunities = \App\Models\Opportunity::with('client')
        ->when(auth()->user()->isSales(), fn($q) => $q->where('sales_id', auth()->id()))
        ->orderByDesc('created_at')
        ->get();

    $client = null;

    echo "Rendering activities.create view...\n";
    $html = view('activities.create', compact('clients', 'opportunities', 'opportunity', 'client'))->render();
    echo "Rendered successfully! HTML length: " . strlen($html) . "\n";
} catch (\Throwable $e) {
    echo "ERROR DETECTED:\n";
    echo $e->getMessage() . "\n";
    echo "In file: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
