<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simulate request
$project = \App\Models\Project::findOrFail(1);
$project->load([
    'articles' => function ($q) {
        $q->where('status', 'Published')->orderBy('order_position', 'asc');
    },
    'categories'
]);

$zipFile = public_path($project->slug . '-KB.zip');
$zip = new \ZipArchive();

if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
    try {
        $folderName = $project->slug;
        echo "Rendering view...\n";
        $indexHtml = view('export.index', ['project' => $project, 'isExport' => true])->render();
        echo "Regex match...\n";
        $indexHtml = preg_replace_callback('/src=["\']data:image\/(.*?);base64,(.*?)["\']/is', function ($matches) use ($zip, $folderName) {
            $ext = strtolower($matches[1]);
            if ($ext === 'jpeg') {
                $ext = 'jpg';
            }
            $base64Data = $matches[2];
            $binaryData = base64_decode($base64Data);
            $filename = 'img_' . md5($binaryData) . '.' . $ext;
            $zip->addFromString($folderName . '/images/' . $filename, $binaryData);
            return 'src="images/' . $filename . '"';
        }, $indexHtml);

        $zip->addFromString($folderName . '/index.html', $indexHtml);
        if (!$zip->close()) {
            echo "Failed to close ZIP: " . $zip->status . "\n";
        } else {
            echo "ZIP generated successfully at " . $zipFile . "\n";
        }
    } catch (\Throwable $e) {
        echo "Exception: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString();
    }
} else {
    echo "Failed to open ZIP!\n";
}
