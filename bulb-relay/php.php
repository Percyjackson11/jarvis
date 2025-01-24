<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bulb1 = $_POST["bulb1"];
    $bulb2 = $_POST["bulb2"];
    
    $instructions = "$bulb1,$bulb2";
    file_put_contents("bulb_instructions.txt", $instructions);
}
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Bulb Control</h2>
    <form method="post">
        Bulb 1:
        <input type="radio" name="bulb1" value="on" required> On
        <input type="radio" name="bulb1" value="off" required> Off
        <br><br>
        Bulb 2:
        <input type="radio" name="bulb2" value="on" required> On
        <input type="radio" name="bulb2" value="off" required> Off
        <br><br>
        <input type="submit" value="Update Bulbs">
    </form>
</body>
</html>