<?php

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

// Usage example
$url = 'https://www.youtube.com/watch?v=F0GQ0l2NfHA'; // Replace with the desired YouTube video URL
$result = getYouTubeCaptions($url);

if ($result !== false) {
    // Store captions in a text file
    $filename = 'captions_' . $result['videoId'] . '_' . $result['language'] . '.txt';
    file_put_contents($filename, $result['captions']);
    echo "Captions saved to $filename (Language: {$result['language']})";
} else {
    echo "Failed to retrieve captions.";
}

?>