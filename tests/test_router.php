<?php
require_once 'bootstrap.php';

// List all controller files
echo "<h1>Controller Files</h1>";
echo "<ul>";
$controllers = glob(CONTROLLER_PATH . "/*.php");
foreach ($controllers as $controller) {
    echo "<li>" . basename($controller) . "</li>";
}
echo "</ul>";

// List all routes
echo "<h1>Available Routes</h1>";
echo "<ul>";
$view_dirs = glob(VIEW_PATH . "/*", GLOB_ONLYDIR);
foreach ($view_dirs as $dir) {
    $dir_name = basename($dir);
    echo "<li><a href='/index.php/{$dir_name}'>{$dir_name}</a></li>";
}
echo "</ul>";
