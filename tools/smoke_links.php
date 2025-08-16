<?php
require __DIR__ . '/../includes/bootstrap.php';

$urls = [
    urlp('assets/css/styles.css'),
    urlp('assets/css/utilities.css'),
    urlp('assets/js/app.js'),
    urlp('public/index.php'),
    urlp('admin/index.php')
];

foreach ($urls as $u) {
    $ch = curl_init('http://localhost:3000' . $u);
    curl_setopt_array($ch, [CURLOPT_NOBODY => true, CURLOPT_RETURNTRANSFER => true]);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "$u => $code\n";
}
