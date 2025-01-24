<style>
.container {  
    max-width: 1100px;  
    margin: auto;  
    padding: 20px;  
}

.logo {  
    font-size: 24px;  
    color: #000;  
}

.logo span {  
    color: #777;  
}

a {  
    color: #000; /* Ensures links are black */  
}

a:hover {  
    color: #777; /* Changes link color on hover for visual feedback */
}  

.form-container {  
    margin-bottom: 20px;
    margin-top: 20px;
}

.search-form, .url-form {  
    display: flex;  
    margin-bottom: 20px;  
}  

.search-form input[type="text"], .url-form input[type="text"] {  
    flex: 1;  
    padding: 10px;  
    font-size: 16px;  
    border: 2px solid #ddd;  
    border-radius: 20px 0 0 20px;  
    outline: none;  
}  

.search-form button, .url-form button {  
    padding: 10px 20px;  
    font-size: 16px;  
    border: none;  
    background-color: #000;  
    color: #fff;  
    border-radius: 0 20px 20px 0;  
    cursor: pointer;  
}  

.search-form button:hover, .url-form button:hover {  
    background-color: #333;  
}  

hr {  
    margin-top: 20px;  
    border: 0;  
    height: 1px;  
    background-color: #ccc;  
}  

h1 {  
font-size: 40px;
}

.article-content {  
margin-top: 20px; 
line-height: 37px; 
font-family: Georgia; 
font-size: 20px;
}

.error {  
    color: red;  
}  
.article-content img {
    max-width: 100%; /* Ensures images are not wider than their container */
    height: auto; /* Maintains aspect ratio */
    display: block; /* Prevents inline display issues */
    margin: 0 auto; /* Centers images */
}

</style>
<?php

require_once('vendor/autoload.php');    

use fivefilters\Readability\Readability;    
use fivefilters\Readability\Configuration;    
use fivefilters\Readability\ParseException;

if(isset($_GET['a'])) {  
    $article_url = $_GET["a"];  
} else {  
    echo "URL parameter 'a' is missing.";  
    exit();  
}

if(substr( $article_url, 0, 23 ) == "https://news.google.com") {
        $google_redirect_page = file_get_contents($article_url);
        $parts = explode('<a href="', $google_redirect_page);
        $actual_article_url = explode('"',$parts[1])[0];
        $article_url = $actual_article_url; 
}

$url = parse_url($article_url);
$host = $url['host'];

$response = file_get_contents($article_url);

    $dom = new DOMDocument();    
    if ($dom->loadHTML($response)) {  
        libxml_clear_errors();    

        $configuration = new Configuration();
        $configuration->setFixRelativeURLs(true);
        $configuration->setOriginalURL('http://' . $host);
        $readability = new Readability($configuration);    
        try {  
            if ($readability->parse($response)) {  
                $title = $readability->getTitle(); // Set the title
                $readable_article = strip_tags($readability->getContent(), '<table><hr><a><img><ol><ul><li><br><p><small><font><b><strong><i><em><blockquote><h1><h2><h3><h4><h5><h6>');
                $readable_article = str_replace( 'strong>', 'b>', $readable_article ); //change <strong> to <b>
                $readable_article = str_replace( 'em>', 'i>', $readable_article ); //change <em> to <i>
                $readable_article = str_replace( 'href="http', 'href="./read.php?a=http', $readable_article ); 
            } else {  
                $error_text = "Failed to parse the article content.";  
            }  
        } catch (Exception $e) {  
            $error_text = "Failed to parse the article content: " . $e->getMessage();  
        }  
    }
?>

<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title><?php echo htmlspecialchars($title); ?></title>  
    <link rel="stylesheet" href="style.css"> <!-- Ensure you link to your CSS file -->  
</head>  
<body>  
    <div class="container">  
        <header>  
            <a href="./" class="logo">Jarvis<span>Search!</span></a>  <br><br>
        </header>  
        <div class="form-container">  
            <form action="./read.php" method="get" class="url-form">  
                <input type="text" name="a" placeholder="Enter A Browsing URL..." value="<?php echo htmlspecialchars($article_url); ?>">  
                <button type="submit">Go!</button>  
            </form>  
            <form action="./" method="get" class="search-form">  
                <input type="text" name="q" placeholder="Search For...">  
                <button type="submit">Search!</button>  
            </form>
        </div>  
        <br>  
        <article>  
            <h1><?php echo htmlspecialchars($title); ?></h1>  
            <?php if($error_text) { echo "<p class='error'>" . htmlspecialchars($error_text) . "</p>"; } ?>  
            <div class="article-content"><?php echo $readable_article; ?></div>  
        </article>  
    </div>  
</body>  
</html>