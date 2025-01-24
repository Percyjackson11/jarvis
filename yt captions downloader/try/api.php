<?php
header('Content-Type: application/json');

function getYouTubeCaptions($url) {
    // Extract video ID from URL
    parse_str(parse_url($url, PHP_URL_QUERY), $params);
    $videoId = $params['v'] ?? null;

    if (!$videoId) {
        return false;
    }

    // Construct the YouTube video URL
    $videoUrl = "https://www.youtube.com/watch?v=" . $videoId;

    // Send a GET request to the YouTube video URL
    $response = file_get_contents($videoUrl);

    // Search for "captionTracks" in the response
    if (preg_match('/"captionTracks":(\[.*?\])/', $response, $matches)) {
        $captionTracks = json_decode($matches[1], true);

        // Find the appropriate caption track
        $selectedTrack = null;
        foreach ($captionTracks as $track) {
            if ($track['languageCode'] === 'en') {
                $selectedTrack = $track;
                break;
            } elseif ($track['kind'] === 'asr' && $track['languageCode'] === 'en') {
                $selectedTrack = $track;
                break;
            }
        }

        // If no English track found, use the first available track
        if (!$selectedTrack && !empty($captionTracks)) {
            $selectedTrack = $captionTracks[0];
        }

        if ($selectedTrack) {
            $baseUrl = $selectedTrack['baseUrl'];

            // Decode Unicode characters
            $decodedUrl = html_entity_decode($baseUrl);

            // Replace \u0026 with &
            $decodedUrl = str_replace('\u0026', '&', $decodedUrl);

            // Fetch captions from the decoded URL
            $captions = file_get_contents($decodedUrl);

            // Parse XML captions
            $xml = simplexml_load_string($captions);
            $captionsText = '';

            foreach ($xml->text as $text) {
                $captionsText .= trim((string)$text) . "\n";
            }

            return [
                'videoId' => $videoId,
                'captions' => $captionsText,
                'language' => $selectedTrack['languageCode']
            ];
        }
    }

    return false;
}

function processUserMessage($message, $history, $videoContext) {
    // Check if the message contains a YouTube URL
    if (strpos($message, 'youtube.com') !== false || strpos($message, 'youtu.be') !== false) {
        // Extract URL from message
        preg_match('/(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\S+/', $message, $matches);
if (!empty($matches[0])) {
            $url = $matches[0];
            $captionsResult = getYouTubeCaptions($url);
            
            if ($captionsResult === false) {
                return ['error' => 'Failed to retrieve video captions'];
            }

            // Store captions and return initial summary
            $systemPrompt = "You are an AI assistant that creates concise video summaries. 
            Please provide a clear and comprehensive summary of the following video transcript. 
            Focus on the main points and key takeaways. Format the summary in bullet points.";

            $response = callLLMAPI($systemPrompt, $captionsResult['captions'], []);

            return [
                'response' => $response,
                'videoContext' => [
                    'captions' => $captionsResult['captions'],
                    'videoId' => $captionsResult['videoId'],
                    'language' => $captionsResult['language']
                ]
            ];
        }
    } else {
        // Handle questions about the video
        if (!$videoContext) {
 //           return ['response' => 'Please share a YouTube video link first before asking questions.'];
        }

        $systemPrompt = "You are an AI assistant answering questions about a video. 
        Using the video transcript provided, answer the following question thoroughly and accurately. 
        If the answer cannot be found in the transcript, please say so.
        
        Video transcript:
        {$videoContext['captions']}
        
        Question: $message";

        $response = callLLMAPI($systemPrompt, $message, $history);
        
        return [
            'response' => $response,
            'videoContext' => $videoContext
        ];
    }
}

function callLLMAPI($systemPrompt, $userMessage, $history) {
    $TOGETHER_API_KEY = '8196c182d375ca69022a1f485cae9c587c1b46f245c8d2e62e799d636896a1e4';

    $messages = [
        ['role' => 'system', 'content' => $systemPrompt],
        ...$history,
        ['role' => 'user', 'content' => $userMessage]
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

// Handle the incoming request
try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['message'])) {
        echo json_encode(['error' => 'No message provided']);
        exit;
    }

    $result = processUserMessage(
        $input['message'],
        $input['history'] ?? [],
        $input['videoContext'] ?? null
    );

    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>