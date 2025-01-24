<style>
div {  
    max-width: 1100px;  
    margin: auto;  
    padding: 20px;  
    line-height: 42px; 
}
a {  
    color: #000;
    text-decoration: none;
}

a:hover {  
    color: #777; /* Changes link color on hover for visual feedback */
} 
body {
margin-top: 20px; 
font-family: Georgia; 
font-size: 20px;
line-height: 32px; 
}
</style>
<div><h2><center>
    <a href="news.php">TOP</a> |
    <a href="news.php?section=world">WORLD</a> |
    <a href="news.php?section=nation">NATION</a> |
    <a href="news.php?section=business">BUSINESS</a> |
    <a href="news.php?section=technology">TECHNOLOGY</a> |
    <a href="news.php?section=entertainment">ENTERTAINMENT</a> |
    <a href="news.php?section=sports">SPORTS</a> |
    <a href="news.php?section=science">SCIENCE</a> |
    <a href="news.php?section=health">HEALTH</a></center><br><hr>

<?php
// Extracting parameters from URL
$section = isset($_GET['section']) ? $_GET['section'] : "";

if ($section) {
    $feed_url = "https://news.google.com/news/rss/headlines/section/topic/" . strtoupper($section) . "?ned=IN";
} else {
    $feed_url = "https://news.google.com/rss?gl=IN&hl=EN-IN&ceid=IN:EN";
}

// Fetching and parsing RSS feed
$feed_contents = file_get_contents($feed_url);
$feed = simplexml_load_string($feed_contents);

// Outputting feed items
foreach ($feed->channel->item as $item) {
    // Output title
    echo $item->title . "</h2>";
    
    // Get description
    $description = $item->description;

    // Find all links in description
    preg_match_all('/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1/', $description, $matches);

    // Replace each link with the modified link
    foreach ($matches[2] as $link) {
        $redirect_link = "./read.php?a=" . urlencode($link);
        $description = str_replace($link, $redirect_link, $description);
    }

    // Output modified description
    echo $description . "<hr><h2>";
}
?>
</div>