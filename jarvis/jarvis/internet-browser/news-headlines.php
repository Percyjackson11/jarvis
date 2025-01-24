<?php
header('Content-Type: application/json');

$category = isset($_GET['category']) ? $_GET['category'] : 'top';

if ($category == 'top') {
    $feed_url = "https://news.google.com/rss?gl=IN&hl=EN-IN&ceid=IN:EN";
} else {
    $feed_url = "https://news.google.com/news/rss/headlines/section/topic/" . strtoupper($category) . "?ned=IN";
}

$feed_contents = file_get_contents($feed_url);
$feed = simplexml_load_string($feed_contents);

$headlines = [];
$count = 0;
foreach ($feed->channel->item as $item) {
    if ($count < 5) {
        $headlines[] = (string)$item->title;
        $count++;
    } else {
        break;
    }
}

echo json_encode($headlines);
?>