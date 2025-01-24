<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'))->imageData;
    if ($data) {
        $image = str_replace('data:image/png;base64,', '', $data);
        $image = base64_decode($image);
        $filename = 'drawings/drawing_' . time() . '.png'; // Create a unique filename
        file_put_contents($filename, $image);
        echo 'Image saved successfully.';
    } else {
        echo 'No data received.';
    }
} else {
    echo 'Invalid request method.';
}
?>
