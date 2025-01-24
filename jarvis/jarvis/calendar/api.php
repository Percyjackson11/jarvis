<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'];
$conversationHistory = $input['history'];

// Read the events from a file
$events = readEvents();
$llmResponse = callLLMAPI($userMessage, $events, $conversationHistory);
$result = processLLMResponse($llmResponse);

echo json_encode(['response' => $result['userResponse']]);

function readEvents() {
    $events = [];
    $lines = file('events.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        list($date, $description) = explode('|', $line, 2);
        $events[] = ['date' => $date, 'description' => $description];
    }
    return $events;
}

function writeEvents($eventsText) {
    file_put_contents('events.txt', $eventsText);
}

function callLLMAPI($userMessage, $events, $conversationHistory) {
    $TOGETHER_API_KEY = '8196c182d375ca69022a1f485cae9c587c1b46f245c8d2e62e799d636896a1e4';
    $formattedEvents = formatEventsForPrompt($events);
    
    $currentDateTime = date('Y-m-d H:i:s');
    
    $systemPrompt = "You are an AI assistant managing a calendar. The current date and time is $currentDateTime. The current events are:\n$formattedEvents\n
    Respond to the user's query about the calendar. If any changes are needed (add, remove, or modify events),
    include the entire updated calendar at the end of your response in the following format:

    ---CALENDAR START---
    YYYY-MM-DD|Event description
    YYYY-MM-DD|Another event description
    ---CALENDAR END---

    Only include this calendar section if changes were made. The user will not see this section.

    Please respond appropriately based on the user's query.";

    $messages = [
        ['role' => 'system', 'content' => $systemPrompt],
        ...$conversationHistory
    ];

$response = file_get_contents('https://api.together.xyz/chat/completions', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                "Authorization: Bearer $TOGETHER_API_KEY"
            ],
            'content' => json_encode([
                'model' => 'meta-llama/Llama-3-70b-chat-hf',
                'messages' => $messages
            ])
        ]
    ]));

    if ($response === false) {
        throw new Exception('Failed to get response from API');
    }

    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Failed to decode API response');
    }

    return $result['choices'][0]['message']['content'];
}

function processLLMResponse($llmResponse) {
    $result = ['userResponse' => $llmResponse];

    if (preg_match('/---CALENDAR START---(.*?)---CALENDAR END---/s', $llmResponse, $matches)) {
        $newCalendar = trim($matches[1]);
        writeEvents($newCalendar);
        $result['userResponse'] = str_replace($matches[0], '', $llmResponse);
    }

    return $result;
}

function formatEventsForPrompt($events) {
    $formattedEvents = "";
    foreach ($events as $event) {
        $formattedEvents .= "{$event['date']}|{$event['description']}\n";
    }
    return $formattedEvents;
}
?>