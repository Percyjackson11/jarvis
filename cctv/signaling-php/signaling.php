<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Directory to store stream data
$streamDir = 'streams/';
if (!file_exists($streamDir)) {
    mkdir($streamDir, 0777, true);
}

// Clean up old streams (older than 1 hour)
cleanup($streamDir);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'];

    if ($action === 'create') {
        // Generate a unique 6-digit code
        do {
            $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (file_exists($streamDir . $code . '.json'));

        // Save offer data
        $streamData = [
            'offer' => $data['offer'],
            'timestamp' => time(),
            'answer' => null
        ];
        
        file_put_contents($streamDir . $code . '.json', json_encode($streamData));
        echo json_encode(['code' => $code]);
    }
    else if ($action === 'answer') {
        $code = $data['code'];
        $filePath = $streamDir . $code . '.json';
        
        if (file_exists($filePath)) {
            $streamData = json_decode(file_get_contents($filePath), true);
            $streamData['answer'] = $data['answer'];
            file_put_contents($filePath, json_encode($streamData));
            echo json_encode(['status' => 'success']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Stream not found']);
        }
    }
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $code = $_GET['code'];
    $type = $_GET['type'];
    $filePath = $streamDir . $code . '.json';

    if (file_exists($filePath)) {
        $streamData = json_decode(file_get_contents($filePath), true);
        
        if ($type === 'offer') {
            echo json_encode(['offer' => $streamData['offer']]);
        }
        else if ($type === 'answer') {
            echo json_encode(['answer' => $streamData['answer']]);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Stream not found']);
    }
}

// Function to clean up old stream files
function cleanup($dir) {
    $files = glob($dir . '*.json');
    $now = time();
    
    foreach ($files as $file) {
        $streamData = json_decode(file_get_contents($file), true);
        // Delete files older than 1 hour
        if ($now - $streamData['timestamp'] > 3600) {
            unlink($file);
        }
    }
}
?>