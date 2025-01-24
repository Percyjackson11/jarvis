<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the content from the POST request
    $content = $_POST['content'];

    // Check if the "saves" directory exists, if not, create it
    if (!is_dir('saves')) {
        mkdir('saves');
    }

    // Find the next available filename
    $files = glob('saves/*.html');
    $nextFilename = 'saves/' . (count($files) + 1) . '.html';

    // Add timestamp
    $timestamp = date('Y-m-d H:i:s');

    // Add the timestamp to the content
    $content .= "<p>Saved at: $timestamp</p>";

    // Save the content to a file
    file_put_contents($nextFilename, $content);

    // Output success message
    echo "Content saved successfully as <a href='$nextFilename'>$nextFilename</a>.";
    exit; // Stop further execution
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Save Content as HTML</title>
    <style>
        #editable {
            border: 1px solid #ccc;
            min-height: 200px;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div id="editable" contenteditable="true"></div>
    <br>
    <button id="saveBtn">Save as HTML</button>

    <script>
        // Function to handle saving content as HTML
        function saveContent() {
            var content = document.getElementById('editable').innerHTML;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', window.location.href, true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert(xhr.responseText);
                }
            };
            xhr.send('content=' + encodeURIComponent(content));
        }

        // Attach click event to the save button
        document.getElementById('saveBtn').addEventListener('click', saveContent);
    </script>
</body>
</html>
