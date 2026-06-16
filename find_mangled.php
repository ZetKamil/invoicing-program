<?php
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('.'));
foreach ($iterator as $file) {
    if ($file->isFile() && in_array($file->getExtension(), ['php'])) {
        $path = $file->getPathname();
        if (strpos($path, 'vendor') !== false || strpos($path, 'node_modules') !== false) {
            continue;
        }
        $content = file_get_contents($path);
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            echo "BOM: $path\n";
        }
        if (strpos($content, 'Ã') !== false || strpos($content, 'Å') !== false) {
            echo "MANGLED: $path\n";
        }
    }
}
