<?php

function getYouTubeCaptions($videoId) {
    // Construct the YouTube video URL
    $videoUrl = "https://www.youtube.com/watch?v=" . $videoId;

    // Send a GET request to the YouTube video URL
    $response = file_get_contents($videoUrl);

    // Search for "captionTracks" in the response
    if (preg_match('/"captionTracks":(\[.*?\])/', $response, $matches)) {
        $captionTracks = json_decode($matches[1], true);

        // Get the baseUrl from the first item in captionTracks
        if (!empty($captionTracks) && isset($captionTracks[0]['baseUrl'])) {
            $baseUrl = $captionTracks[0]['baseUrl'];

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

            return $captionsText;
        }
    }

    return false;
}

// Usage example
$videoId = 'UPrkC1LdlLY'; // Replace with the desired YouTube video ID
$captions = getYouTubeCaptions($videoId);

if ($captions !== false) {
    // Store captions in a text file
    $filename = 'captions_' . $videoId . '.txt';
    file_put_contents($filename, $captions);
    echo "Captions saved to $filename";
} else {
    echo "Failed to retrieve captions.";
}

?>