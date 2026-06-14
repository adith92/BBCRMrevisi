<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Simulating saving an activity...\n";
    $sales = \App\Models\User::where('role', 'sales')->first();
    $client = \App\Models\Client::first();
    $opportunity = \App\Models\Opportunity::first();

    $validated = [
        'type'             => 'meeting',
        'subject'          => 'Test Meeting Subject',
        'activity_date'    => now()->toDateTimeString(),
        'client_id'        => $client->id,
        'opportunity_id'   => $opportunity->id,
        'duration_minutes' => 60,
        'outcome'          => 'Test outcome',
        'next_action'      => 'Test next action',
        'next_action_date' => now()->addDay()->toDateString(),
        'notes'            => 'Test notes',
    ];
    $validated['sales_id'] = $sales->id;

    $activity = \App\Models\ActivityLog::create($validated);
    echo "Activity successfully created with ID: " . $activity->id . "\n";
    
    // Clean up
    $activity->delete();
    echo "Cleaned up test activity.\n";
} catch (\Throwable $e) {
    echo "ERROR DETECTED:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
