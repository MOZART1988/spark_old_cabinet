<?php

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, 
    'http://45.32.153.55/API/public/index.php/v1/leadid/'.$_SERVER['argv'][1]
);
$content = curl_exec($ch);
echo $content;
?>