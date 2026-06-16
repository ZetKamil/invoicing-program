<?php
$replacements = [
    'â€”' => '—',
    'â‚¬' => '€',
    'âś“' => '✓',
    'đźŽŻ' => '🎯',
    'â˜…' => '★',
    'âšˇ' => '⚡',
    'đź“ž' => '📞',
    'đź“„' => '📄',
    'đźŚ ' => '🕸 ',
    'đźš›' => '🚛',
    'đź“¦' => '📦',
    'đź“¬' => '📬',
    'đź””' => '🔔',
    'đź“¤' => '📤',
    'đź“Ť' => '📫',
    'đź“§' => '📧',
    'đź’Ľ' => '💥',
    'âŹ±ď¸Ź' => '⏱️',
    'âŹł' => '⏳',
    'Ă—' => '×',
    'Ă©' => 'é',
    'ĂŻ' => 'ï',
    'â†’' => '→',
    'đź”Ť' => '🔍',
    'Ä™' => 'ę',
    'Ĺ›' => 'ś',
    'Ĺ‚' => 'ł',
    'Ä…' => 'ą',
    'Ăł' => 'ó',
    'Ĺş' => 'ź',
    'ĹĽ' => 'ż',
    'Ä‡' => 'ć',
    'Ĺ„' => 'ń',
    'Ä' => 'Ę',
    'Ĺš' => 'Ś',
    'Ĺ' => 'Ł',
    'Ä„' => 'Ą',
    'Ă“' => 'Ó',
    'Ĺą' => 'Ź',
    'Ĺť' => 'Ż',
    'Ä†' => 'Ć',
    'Ĺƒ' => 'Ń',
    'vĂłĂłr' => 'vóór',
    'geĂŻnteresseerd' => 'geïnteresseerd'
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
