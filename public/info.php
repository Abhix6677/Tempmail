<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

echo "Laravel storage_path('framework/views'): " . storage_path('framework/views') . "<br>";
echo "realpath: " . var_export(realpath(storage_path('framework/views')), true) . "<br>";
echo "is_dir: " . (is_dir(storage_path('framework/views')) ? 'yes' : 'no') . "<br>";
echo "is_writable: " . (is_writable(storage_path('framework/views')) ? 'yes' : 'no') . "<br>";
?>
