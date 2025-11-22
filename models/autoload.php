<?php
// Simple autoloader for Models namespace
spl_autoload_register(function ($class) {
    // Only handle Models namespace
    if (strpos($class, 'Models\\') !== 0) {
        return;
    }
    
    // Remove namespace prefix
    $class = substr($class, 7); // Remove "Models\" (7 chars)
    
    // Convert namespace separators to directory separators
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    
    // Convert class name to file name
    $file = __DIR__ . '/' . $class . '.php';
    
    // Load the file if it exists
    if (file_exists($file)) {
        require_once $file;
    }
});
