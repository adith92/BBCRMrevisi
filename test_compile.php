<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Compiling activities.create...\n";
    $compiler = app('blade.compiler');
    $path = resource_path('views/activities/create.blade.php');
    $compiledPath = $compiler->getCompiledPath($path);
    
    // Force compile
    $compiler->compile($path);
    echo "Compiled file: $compiledPath\n";
    
    // Lint the compiled file
    $output = [];
    $return_var = 0;
    exec("php -l " . escapeshellarg($compiledPath), $output, $return_var);
    echo implode("\n", $output) . "\n";
    if ($return_var === 0) {
        echo "Blade compilation syntax is VALID!\n";
    } else {
        echo "Blade compilation syntax is INVALID!\n";
    }
} catch (\Throwable $e) {
    echo "ERROR:\n" . $e->getMessage() . "\n";
}
