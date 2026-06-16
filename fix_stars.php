<?php
$content = file_get_contents('resources/views/pages/home.blade.php');
$content = str_replace('â˜…', '★', $content);
file_put_contents('resources/views/pages/home.blade.php', $content);
echo "Fixed stars!";
