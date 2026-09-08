<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$zipFile = public_path('test-KB.zip');
$zip = new \ZipArchive();
$res = $zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

echo "Open Result: ";
var_dump($res);

if ($res === true) {
    if (!$zip->addFromString('test.txt', 'test content')) {
        echo "addFromString Failed!\n";
    }
    if (!$zip->close()) {
        echo "Close Failed! (Status: " . $zip->status . ", Sys: " . $zip->statusSys . ")\n";
    } else {
        echo "Success!\n";
    }
}
