<?php
header('Content-Type: application/json');

// Read the bulb states from a file or database
// For this example, we'll use a file
$filename = 'bulb_states.txt';

if (file_exists($filename)) {
    $content = file_get_contents($filename);
    $states = explode(',', $content);
    
    if (count($states) == 2) {
        $response = [
            'bulb1' => trim($states[0]),
            'bulb2' => trim($states[1])
        ];
    } else {
        $response = [
            'error' => 'Invalid data format in file'
        ];
    }
} else {
    $response = [
        'error' => 'File not found'
    ];
}

echo json_encode($response);
?>