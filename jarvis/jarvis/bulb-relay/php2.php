<?php
$bulb_states = file_exists("bulb_instructions.txt") ? explode(",", file_get_contents("bulb_instructions.txt")) : ["off", "off"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["toggle1"])) {
        $bulb_states[0] = $bulb_states[0] == "on" ? "off" : "on";
    } elseif (isset($_POST["toggle2"])) {
        $bulb_states[1] = $bulb_states[1] == "on" ? "off" : "on";
    }
    file_put_contents("bulb_instructions.txt", implode(",", $bulb_states));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulb Control</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            height: 100%;
            font-family: Arial, sans-serif;
        }
        body {
            display: flex;
            flex-direction: column;
            background-color: #f0f0f0;
        }
        .container {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            padding: 1rem;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 1rem;
            font-size: 5rem; /* Increased font size */
        }
        form {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-around;
        }
        .toggle-btn {
            flex-grow: 1;
            border: none;
            color: white;
            padding: 40px; /* Increased padding */
            text-align: center;
            text-decoration: none;
            font-size: 3rem; /* Increased font size */
            margin: 1rem 0; /* Increased margin */
            cursor: pointer;
            border-radius: 20px; /* Increased border radius */
            transition: background-color 0.3s;
        }
        .toggle-btn[value$="On"] {
            background-color: #f44336; /* Inverted to red */
        }
        .toggle-btn[value$="On"]:hover {
            background-color: #da190b; /* Inverted to darker red */
        }
        .toggle-btn[value$="Off"] {
            background-color: #4CAF50; /* Inverted to green */
        }
        .toggle-btn[value$="Off"]:hover {
            background-color: #45a049; /* Inverted to darker green */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bulb Control</h1>
        <form method="post">
            <button type="submit" name="toggle1" class="toggle-btn" value="Turn <?php echo $bulb_states[0] == 'on' ? 'Off' : 'On'; ?>">
                Bulb 1: <?php echo ucfirst($bulb_states[0]); ?>
            </button>
            <button type="submit" name="toggle2" class="toggle-btn" value="Turn <?php echo $bulb_states[1] == 'on' ? 'Off' : 'On'; ?>">
                Bulb 2: <?php echo ucfirst($bulb_states[1]); ?>
            </button>
        </form>
    </div>
</body>
</html>
