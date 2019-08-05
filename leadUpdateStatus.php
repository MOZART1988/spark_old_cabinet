<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, 
    'http://192.168.0.26/API/public/index.php/v1/cronUpdateStatus'
);
$content = curl_exec($ch);
echo $content;