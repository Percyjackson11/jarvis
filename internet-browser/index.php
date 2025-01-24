<?php
require_once('vendor/autoload.php');

$show_results = FALSE;
$results_html = "";
$final_result_html = "<hr><br>";

if(isset( $_GET['q'])) { // if there's a search query, show the results for it
    $query = urlencode($_GET["q"]);
    $show_results = TRUE;
    $search_url = "https://html.duckduckgo.com/html?q=" . $query;
    if(!$results_html = file_get_contents($search_url)) {
        $error_text .=  "Failed to get results, sorry :( <br>";
    }
    $simple_results=$results_html;
    $simple_results = str_replace( 'strong>', 'b>', $simple_results ); //change <strong> to <b>
    $simple_results = str_replace( 'em>', 'i>', $simple_results ); //change <em> to <i>
    $simple_results = clean_str($simple_results);

    $result_blocks = explode('<h2 class="result__title">', $simple_results);
    $total_results = count($result_blocks)-1;

    for ($x = 1; $x <= $total_results; $x++) {
        if(strpos($result_blocks[$x], '<a class="badge--ad">')===false) { //only return non ads
            // result link, redirected through our proxy
            $result_link = explode('class="result__a" href="', $result_blocks[$x])[1];
            $result_topline = explode('">', $result_link);
            $result_link = str_replace( '//duckduckgo.com/l/?uddg=', './read.php?a=', $result_topline[0]);
            // result title
            $result_title = str_replace("</a>","",explode("\n", $result_topline[1]));
            // result display url
            $result_display_url = explode('class="result__url"', $result_blocks[$x])[1];
            $result_display_url = trim(explode("\n", $result_display_url)[1]);
            // result snippet
            $result_snippet = explode('class="result__snippet"', $result_blocks[$x])[1];
            $result_snippet = explode('">', $result_snippet)[1];
            $result_snippet = explode('</a>', $result_snippet)[0];

            $final_result_html .= "<a href='" . $result_link . "'><b>" . $result_title[0] . "</b><br><font color='#262626' >" 
                                . $result_display_url . "</font></a><br><br>" . $result_snippet . "<br><br><hr><br>";
        }
    }
}

//replace chars that old machines probably can't handle
function clean_str($str) {
    $str = str_replace( "‘", "'", $str );    
    $str = str_replace( "’", "'", $str );  
    $str = str_replace( "“", '"', $str ); 
    $str = str_replace( "”", '"', $str );
    $str = str_replace( "–", '-', $str );
    $str = str_replace( "&#x27;", "'", $str );

    return $str;
}

?>
<style>

body {  
    line-height: 1.5; /* Increased line spacing */  
    color: rgb(36, 36, 36);
    font-family: 'cambria', sans-serif;
    font-size: 20px;
    letter-spacing: -0.06px;
    background-color: rgb(255, 255, 255);
}  

.container {
    max-width: 800px;
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

button {
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

.results h2, .welcome h1 {
    margin-bottom: 20px;
    color: #777;
}

.results h2 span, .welcome h1 {
    color: #000;
}
a {  
    color: #000; /* Ensures links are black */  
}  

a:hover {  
    color: #777; /* Changes link color on hover for visual feedback */
} 

</style>
<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Jarvis internet browser</title>  
    <link rel="stylesheet" href="style.css"> <!-- Link to your CSS file -->  
</head>  
<body>  
    <div class="container">  
        <header>  
            <a href="./" class="logo">Jarvis<span>Search!</span></a>  <br><br>
        </header>  
        <main>  
            <?php if($show_results): ?>  
                <form action="./" method="get" class="search-form">  
                    <input type="text" name="q" value="<?php echo urldecode($query); ?>" placeholder="Search again...">  
                    <button type="submit">Search!</button>  
                </form>  
                <div class="results">  
                    <h2>Search Results for <span><?php echo strip_tags(urldecode($query)); ?></span></h2>  
                    <?php echo $final_result_html; ?>  
                </div>  
            <?php else: ?>  
                <div class="welcome">  
                    <h1>The Search Engine for Jarvis</h1>  <br>
                    <form action="./" method="get" class="search-form">  
                        <input type="text" name="q" placeholder="Search For...">  
                        <button type="submit">Search!</button>  
                    </form>  
                    <form action="./read.php" method="get" class="url-form">  
                        <input type="text" name="a" placeholder="Enter A Browsing URL...">  
                        <button type="submit">Go!</button>  
                    </form><br>
                    <h2><center><a href="news.php">Or Read Latest News Here!</a></center></h2>
                </div>  
            <?php endif; ?>  
        </main>  
    </div>  
</body>  
</html>