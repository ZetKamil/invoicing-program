<?php
$replacements = [
    'Â·' => '·',
    'Ă©' => 'é', // Just in case
    'âś…' => '✅', // Checkmark in audit form
    'âšˇ' => '⚡'
];

$directories = ['app', 'resources/views', 'config', 'database', 'routes'];

foreach ($directories as $dir) {
    if (!is_dir($dir)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['php'])) {
            $content = file_get_contents($file->getPathname());
            $changed = false;
            foreach ($replacements as $mangled => $fixed) {
                if (strpos($content, $mangled) !== false) {
                    $content = str_replace($mangled, $fixed, $content);
                    $changed = true;
                }
            }
            if ($changed) {
                echo "Fixed: " . $file->getPathname() . "\n";
                file_put_contents($file->getPathname(), $content);
            }
        }
    }
}
echo "Done!\n";
