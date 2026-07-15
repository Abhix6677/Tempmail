<?php
// Run this script once to combine vendor parts into vendor.zip
$parts = [];
for ($i = 1; $i <= 7; $i++) {
    $file = __DIR__ . '/vendor.part' . str_pad($i, 2, '0', STR_PAD_LEFT) . '.zip';
    if (file_exists($file)) {
        $parts[] = $file;
        echo "Found: $file (" . round(filesize($file)/1024/1024, 2) . " MB)\n";
    }
}
if (count($parts) !== 7) {
    echo "Error: Expected 7 parts, found " . count($parts) . "\n";
    exit(1);
}
$output = __DIR__ . '/vendor.zip';
$out = fopen($output, 'wb');
if (!$out) { echo "Error: Cannot create $output\n"; exit(1); }
foreach ($parts as $part) {
    echo "Merging: $part...\n";
    $in = fopen($part, 'rb');
    if (!$in) { echo "Error: Cannot read $part\n"; exit(1); }
    stream_copy_to_stream($in, $out);
    fclose($in);
}
fclose($out);
echo "Done! Created: $output (" . round(filesize($output)/1024/1024, 2) . " MB)\n";
echo "Now extract vendor.zip using File Manager\n";
