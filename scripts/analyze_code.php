<?php
// PHPStan runner script from scripts directory

// Define colors for terminal output
define('COLOR_GREEN', "\033[32m");
define('COLOR_RED', "\033[31m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_RESET', "\033[0m");

echo COLOR_BLUE . "Running PHPStan code analysis...\n" . COLOR_RESET;

// Determine the full path to phpstan for Windows compatibility
$rootDir = dirname(__DIR__); // Get parent directory (project root)
$vendorBinPath = $rootDir . '/vendor/bin';
$phpstanPath = $vendorBinPath . (PHP_OS === 'WINNT' ? '/phpstan.bat' : '/phpstan');
$configPath = __DIR__ . '/phpstan.neon';

// Check if phpstan executable exists
if (!file_exists($phpstanPath)) {
    echo COLOR_RED . "Error: PHPStan executable not found at $phpstanPath\n" . COLOR_RESET;
    echo "Make sure you've installed PHPStan using Composer.\n";
    exit(1);
}

// Check if configuration file exists
if (!file_exists($configPath)) {
    echo COLOR_RED . "Error: phpstan.neon configuration file not found at $configPath\n" . COLOR_RESET;
    exit(1);
}

// Run PHPStan using absolute paths which works better in all environments
$command = escapeshellarg($phpstanPath) . ' analyse --configuration ' . escapeshellarg($configPath);
$output = [];
$returnVar = 0;

exec($command, $output, $returnVar);

// Display results
if ($returnVar === 0) {
    echo COLOR_GREEN . "✓ No errors found!\n" . COLOR_RESET;
} else {
    echo COLOR_RED . "✗ Found issues:\n" . COLOR_RESET;
    foreach ($output as $line) {
        // Highlight file paths
        $line = preg_replace('/(\/[^\s:]+\.php)/', COLOR_YELLOW . '$1' . COLOR_RESET, $line);
        // Highlight line numbers
        $line = preg_replace('/(\d+):/', COLOR_GREEN . '$1' . COLOR_RESET . ':', $line);
        // Highlight errors
        $line = preg_replace('/(Error:)/', COLOR_RED . '$1' . COLOR_RESET, $line);
        echo $line . "\n";
    }
}

// Specifically look for unused methods
echo COLOR_BLUE . "\nChecking for unused methods...\n" . COLOR_RESET;

// Use a more targeted approach with strict rules using absolute paths
$unusedCommand = escapeshellarg($phpstanPath) . ' analyse --configuration ' . escapeshellarg($configPath) . ' --level=5 --error-format=table';
$unusedOutput = [];
$unusedReturnVar = 0;

exec($unusedCommand, $unusedOutput, $unusedReturnVar);

$unusedMethodCount = 0;
foreach ($unusedOutput as $line) {
    // Check for both private and protected unused methods with different wording patterns
    if (strpos($line, 'is never called.') !== false || 
        strpos($line, 'is unused') !== false ||
        strpos($line, 'dead code') !== false ||
        strpos($line, 'not used') !== false) {
        echo COLOR_YELLOW . $line . COLOR_RESET . "\n";
        $unusedMethodCount++;
    }
}

if ($unusedMethodCount === 0) {
    echo COLOR_GREEN . "✓ No unused methods detected!\n" . COLOR_RESET;
} else {
    echo COLOR_YELLOW . "Found $unusedMethodCount potentially unused methods.\n" . COLOR_RESET;
}

exit($returnVar);
