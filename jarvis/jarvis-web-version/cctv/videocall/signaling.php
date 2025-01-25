<?php
session_start();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$feedCode = $_GET['code'] ?? '';

if ($action == 'create') {
    $_SESSION['feeds'][$feedCode] = [
        'status' => 'waiting',
        'candidates' => []
    ];
    echo json_encode(['status' => 'created']);
}
elseif ($action == 'join') {
    if (isset($_SESSION['feeds'][$feedCode])) {
        echo json_encode($_SESSION['feeds'][$feedCode]);
    } else {
        echo json_encode(['error' => 'Feed not found']);
    }
}
elseif ($action == 'offer') {
    $data = json_decode(file_get_contents('php://input'), true);
    $_SESSION['feeds'][$feedCode]['offer'] = $data['offer'];
    echo json_encode(['status' => 'offer received']);
}
elseif ($action == 'answer') {
    $data = json_decode(file_get_contents('php://input'), true);
    $_SESSION['feeds'][$feedCode]['answer'] = $data['answer'];
    echo json_encode(['status' => 'answer received']);
}
elseif ($action == 'candidate') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($_SESSION['feeds'][$feedCode]['candidates'])) {
        $_SESSION['feeds'][$feedCode]['candidates'] = [];
    }
    $_SESSION['feeds'][$feedCode]['candidates'][] = $data['candidate'];
    echo json_encode(['status' => 'candidate received']);
}
elseif ($action == 'getCandidate') {
    if (isset($_SESSION['feeds'][$feedCode]['candidates'])) {
        $candidates = $_SESSION['feeds'][$feedCode]['candidates'];
        $_SESSION['feeds'][$feedCode]['candidates'] = []; // Clear after sending
        echo json_encode(['candidates' => $candidates]);
    } else {
        echo json_encode(['candidates' => []]);
    }
}
else {
    echo json_encode(['error' => 'Invalid action']);
}
?>