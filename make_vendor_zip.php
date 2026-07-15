<?php
// Creates a Linux-compatible vendor.zip (forward slashes only)
$vendorDir = __DIR__ . '/vendor';
$outputZip = __DIR__ . '/vendor_linux.zip';

echo "Scanning vendor directory...\n";
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($vendorDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$zip = new ZipArchive();
if ($zip->open($outputZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Cannot create $outputZip\n");
}

$count = 0;
foreach ($files as $file) {
    if (!$file->isFile()) continue;
    // Get relative path with forward slashes
    $relativePath = 'vendor/' . str_replace('\\', '/', substr($file->getPathname(), strlen($vendorDir) + 1));
    $zip->addFile($file->getPathname(), $relativePath);
    $count++;
    if ($count % 5000 === 0) {
        echo "Added $count files...\n";
    }
}
$zip->close();

echo "Done! Added $count files to vendor_linux.zip\n";
echo "Size: " . round(filesize($outputZip) / 1024 / 1024, 2) . " MB\n";
echo "Path: $outputZip\n";
