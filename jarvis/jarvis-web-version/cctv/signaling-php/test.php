<?php
if (isset($_COOKIE['__test'])) {
    header('Content-Type: text/plain');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header('Access-Control-Allow-Headers: Content-Type');
    
    echo "TEST_OK";
    exit;
}
?>