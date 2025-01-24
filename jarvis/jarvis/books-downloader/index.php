<?php
if (isset($_POST['submitBook']) && isset($_FILES['bookFile'])) {
    $targetDir = "books/unread/";
    $fileName = basename($_FILES["bookFile"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION);

    // You can add file validation here (e.g., file types, file size)

    // Move the file to the unread folder
    if(move_uploaded_file($_FILES["bookFile"]["tmp_name"], $targetFilePath)){
        echo "The file ". htmlspecialchars($fileName). " has been uploaded.";
    } else{
        echo "There was an error uploading your file.";
    }
}
// Function to encode only the query string part of the URL
function encodeQueryString($url) {
    $parts = parse_url($url);
    if(isset($parts['query'])) {
        $query = $parts['query'];
        $encodedQuery = urlencode($query);
        $url = str_replace($query, $encodedQuery, $url);
    }
    return $url;
}

function searchLibgen($bookName) {  
    $url = 'https://libgen.li/index.php?req=' . urlencode($bookName) . '+ext:' . $_GET['file_extension'] . '+lang:eng&gmode=on&res=100';
    $html = file_get_contents($url);  
    // Extracting data from HTML table  
    $dom = new DOMDocument();  
    @$dom->loadHTML($html);  
    $xpath = new DOMXPath($dom);  
    $data = array();  
    $secondTable = $xpath->query('(//table)[2]')->item(0);  
    $rows = $xpath->query('.//tr', $secondTable);  
    foreach ($rows as $row) {  
        $cols = $row->getElementsByTagName('td');  
        if ($cols->length > 1) {  
            // Apply filter to the entire first column content  
            $firstColContent = $cols->item(0)->ownerDocument->saveHTML($cols->item(0));  
            $filteredFirstColContent = filterTitle($firstColContent);  
            // Load the filtered content back into a DOMElement to extract the title  
            $filteredDom = new DOMDocument();  
            @$filteredDom->loadHTML($filteredFirstColContent);  
            $filteredTitle = $filteredDom->textContent; // Extract text content as the title  

            $author = $cols->item(1)->nodeValue;  
            $title = $filteredTitle; // Use the filtered title  
            $size = $cols->item(6)->nodeValue;  
            $format = $cols->item(7)->nodeValue;  
            $mirror = modifyLinks($cols->item(8)->ownerDocument->saveHTML($cols->item(8)), $title, $format); // Pass title as an argument  
            $data[] = array('title' => $title, 'author' => $author, 'size' => $size, 'format' => $format, 'mirror' => $mirror);  
        }  
    }  
    return $data;  
}

function filterTitle($title) {  
    // Remove HTML tags  
    $cleanedTitle = strip_tags($title);  

    // Remove any string that contains more than 4 digits  
    $cleanedTitle = preg_replace('/\b\w*[\d]{5,}\w*\b/', '', $cleanedTitle);  

    // Remove any single alphabet character that appears as a single letter, but allow single digits  
    $cleanedTitle = preg_replace('/\b[a-zA-Z]\b(?!\d)/', '', $cleanedTitle);  

    // Normalize spaces (more than one space to one space)  
    $cleanedTitle = preg_replace('/\s+/', ' ', $cleanedTitle);  

    // Remove other special characters except for allowed punctuation  
    // This regex keeps alphanumeric characters, spaces, digits, and a few specific punctuation marks  
    $cleanedTitle = preg_replace('/[^\w\s\d\[\]\'\"().,]/u', '', $cleanedTitle);  

    return trim($cleanedTitle);  
}

// Function to modify links  
function modifyLinks($html, $title, $format) {  
    $dom = new DOMDocument();  
    @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);  
    $links = $dom->getElementsByTagName('a');  

    if ($links->length > 0) {  
        // Clone the first link to preserve it  
        $firstLink = $links->item(0)->cloneNode(true);  
        $firstLink->nodeValue = 'Save to library';
        $href = $firstLink->getAttribute('href');  
        $firstLink->setAttribute('href', 'index.php?url=' . urlencode('https://libgen.li' . $href) . '&title=' . urlencode($title) . '&format=' . urlencode($format)); // Include title in the URL

        // Clear the DOM to remove all elements  
        while ($dom->firstChild) {  
            $dom->removeChild($dom->firstChild);  
        }  

        // Append only the modified first link to the DOM  
        $dom->appendChild($firstLink);  
    } else {  
        // If there are no links, ensure the DOM is empty  
        while ($dom->firstChild) {  
            $dom->removeChild($dom->firstChild);  
        }  
    }  

    // Return the HTML of the modified DOM, which now contains only the first link  
    return $dom->saveHTML();  
}

// Check if the URL parameter is set for downloading
if(isset($_GET['url'])) {

$url = $_GET['url'];  
$ch = curl_init();  
curl_setopt($ch, CURLOPT_URL, $url); // URL to fetch  
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return transfer as a string  
$html = curl_exec($ch);
curl_close($ch);
preg_match('/<a\s+href="([^"]*)"\s*>(.*?)GET(.*?)<\/a>/', $html, $matches);  
$href = $matches[1];  
$href = "https://libgen.li/" . $href;

function sanitizeFileName($fileName) {
    // Replace any character that is not alphanumeric, dash, underscore, or space with an underscore
    $sanitized = preg_replace('/[^a-zA-Z0-9-_ ]/', '_', $fileName);
    // Trim spaces from the beginning and end of the sanitized string
    $sanitized = trim($sanitized);
    // Replace spaces with underscores
    $sanitized = str_replace(' ', '_', $sanitized);
    return $sanitized;
}
$title = "books/unread/" . sanitizeFileName($_GET['title']) . "." . $_GET['format'];
file_put_contents($title, file_get_contents($href));
header('Location: index.php');
exit;
}
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['file'])) {
    $fileToDelete = basename($_GET['file']);
    $folder = $_GET['folder'] === 'read' ? 'read' : 'unread'; // Ensure the folder is either 'read' or 'unread'
    $filePath = __DIR__ . "/books/$folder/" . $fileToDelete;
    if (file_exists($filePath)) {
        unlink($filePath); // Delete the file
        echo "File deleted successfully.";
    } else {
        echo "File does not exist.";
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '?folder=' . $folder);
    exit;
}
// Define the action if any (mark as read/unread)
if (isset($_GET['action']) && isset($_GET['file'])) {
    $action = $_GET['action'];
    $file = basename($_GET['file']);
    if ($action === 'markRead') {
        rename("books/unread/$file", "books/read/$file");
    } elseif ($action === 'markUnread') {
        rename("books/read/$file", "books/unread/$file");
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '?folder=' . ($_GET['folder'] ?? 'unread'));
    exit;
}
// Determine which folder to show
$folderToShow = isset($_GET['folder']) && $_GET['folder'] === 'read' ? 'read' : 'unread';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Search and Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1, h2 {
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            padding: 6px 12px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .file-upload {
            margin-bottom: 20px;
        }
        .file-upload h2 {
            margin-bottom: 10px;
        }
        .file-upload input[type="file"] {
            margin-bottom: 10px;
        }
        .search-form {
            margin-bottom: 20px;
        }
        .search-form label {
            display: block;
            margin-bottom: 5px;
        }
        .search-form input[type="text"] {
            padding: 6px;
            width: 200px;
        }
        .search-form input[type="submit"] {
            padding: 6px 12px;
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .search-form input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .folder-link {
            margin-bottom: 10px;
        }
        .book-table {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Book Search and Management</h1>
    <div class="navigation">
        <a href="index.php">Go to Library</a> | <a href="#" onclick="document.getElementById('fileUpload').style.display='block';return false;">Upload File</a>
    </div>
    <div id="fileUpload" class="file-upload" style="display:none;">
        <h2>Upload a New Book</h2>
        <form action="" method="post" enctype="multipart/form-data">
            Select file to upload:
            <input type="file" name="bookFile" id="bookFile">
            <input type="submit" value="Upload Book" name="submitBook">
        </form>
    </div>
    <div class="search-form">
        <h2>Search for a Book</h2>
        <form method="get" action="">
            <label for="book_name">Enter Book Name:</label>
            <input type="text" id="book_name" name="book_name" value="<?php echo isset($_GET['book_name']) ? htmlspecialchars($_GET['book_name']) : ''; ?>">
            <input type="text" size="1" id="file_extension" name="file_extension" value="<?php echo isset($_GET['file_extension']) ? htmlspecialchars($_GET['file_extension']) : 'mobi'; ?>">
            <input type="submit" name="submit" value="Search">
        </form>
    </div>
    <?php
    // Check if form is submitted for searching
    if (isset($_GET['submit'])) {
        $bookName = $_GET['book_name'];
        $searchResult = searchLibgen($bookName);
        if (!empty($searchResult)) {
            echo '<table border="1">';
            foreach ($searchResult as $row) {
                echo '<tr>';
                echo '<td>' . $row['title'] . '</td>';
                echo '<td>' . $row['author'] . '</td>';
                echo '<td>' . $row['size'] . '</td>';
                echo '<td>' . $row['format'] . '</td>';
                echo '<td>' . $row['mirror'] . '</td>';
                echo '</tr>';
            }
            echo '</table><br>';
        } else {
            echo "No results found!";
        }
    }

// Book management section
echo '<a href="?folder=' . ($folderToShow === 'unread' ? 'read' : 'unread') . '">Show ' . ($folderToShow === 'unread' ? 'Read' : 'Unread') . ' Books</a><br/><br/>';
echo '<table border="1">';
echo '<tr><th>Name</th><th>Size</th><th>Download</th><th>Action</th><th>Delete</th></tr>'; 
$dir = __DIR__ . "/books/$folderToShow/";
$files = scandir($dir);
// Sort files by modification time, newest first
usort($files, function($a, $b) use ($dir) {
    return filemtime($dir . $b) - filemtime($dir . $a);
});
foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    $fileSize = filesize($dir . $file); // Get the file size
    // Determine the unit of file size for display
    if ($fileSize >= 1048576) { // If file size is greater than or equal to 1 MB
        $fileSizeFormatted = number_format($fileSize / 1048576, 2) . ' MB'; // Format the file size to MB
    } else { // For file sizes less than 1 MB
        $fileSizeFormatted = number_format($fileSize / 1024, 2) . ' KB'; // Format the file size to KB
    }
    echo '<tr>';
    $displayName = str_replace('_', ' ', htmlspecialchars($file));
    echo '<td>' . $displayName . '</td>';
    echo '<td>' . $fileSizeFormatted . '</td>'; // Display the formatted file size
    echo '<td><a href="books/' . $folderToShow . '/' . rawurlencode($file) . '" target="_blank">Download</a></td>';
    $action = $folderToShow === 'unread' ? 'markRead' : 'markUnread';
    $buttonText = $folderToShow === 'unread' ? 'Mark as Read' : 'Mark as Unread';
    echo '<td><a href="?action=' . $action . '&file=' . rawurlencode($file) . '&folder=' . $folderToShow . '">' . $buttonText . '</a></td>';
    echo '<td><a href="?action=delete&file=' . rawurlencode($file) . '&folder=' . $folderToShow . '">Delete</a></td>';
    echo '</tr>';
}
echo '</table><br>';
    ?>
    <div class="folder-link">
        <a href="?folder=<?php echo $folderToShow === 'unread' ? 'read' : 'unread'; ?>" class="button">Show <?php echo $folderToShow === 'unread' ? 'Read' : 'Unread'; ?> Books</a>
    </div>
    <table class="book-table">
        <tr>
            <th>Name</th>
            <th>Size</th>
            <th>Download</th>
            <th>Action</th>
            <th>Delete</th>
        </tr>
</body>
</html>