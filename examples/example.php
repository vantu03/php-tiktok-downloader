<?php
require __DIR__ . '/../vendor/autoload.php';

use DLHub\DLHub;

$dl = new DLHub("https://www.tiktok.com/@damodadroneshow/video/7484399220221316395");
$result = $dl->run(true);
print_r($result);
