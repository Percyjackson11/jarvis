<?php
// Set timezone
date_default_timezone_set('UTC');

// Get current month and year
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Get the first day of the month
$first_day = mktime(0, 0, 0, $month, 1, $year);

// Get the name of the month
$month_name = date('F', $first_day);

// Get the number of days in the month
$num_days = date('t', $first_day);

// Get the day of the week the month starts on (0 = Sunday, 6 = Saturday)
$start_day = date('w', $first_day);

// Load events from file
$events = file('events.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$event_array = [];
foreach ($events as $event) {
    list($date, $description) = explode('|', $event);
    if (!isset($event_array[$date])) {
        $event_array[$date] = [];
    }
    $event_array[$date][] = $description;
}

// Generate calendar HTML
$calendar = "<h2>$month_name $year</h2>";
$calendar .= "<table border='1'>";
$calendar .= "<tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>";

$current_day = 1;
$calendar .= "<tr>";

// Add empty cells for days before the start of the month
for ($i = 0; $i < $start_day; $i++) {
    $calendar .= "<td></td>";
}

// Add days of the month
while ($current_day <= $num_days) {
    if ($start_day == 7) {
        $calendar .= "</tr><tr>";
        $start_day = 0;
    }

    $date = sprintf('%04d-%02d-%02d', $year, $month, $current_day);
    $calendar .= "<td>$current_day";
    
    if (isset($event_array[$date])) {
        foreach ($event_array[$date] as $event) {
            $calendar .= "<br><small>$event</small>";
        }
    }
    
    $calendar .= "</td>";

    $current_day++;
    $start_day++;
}

// Add empty cells for days after the end of the month
while ($start_day < 7) {
    $calendar .= "<td></td>";
    $start_day++;
}

$calendar .= "</tr></table>";

// Navigation links
$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

$navigation = "<a href='?month=$prev_month&year=$prev_year'>Previous Month</a> | ";
$navigation .= "<a href='?month=" . date('n') . "&year=" . date('Y') . "'>Current Month</a> | ";
$navigation .= "<a href='?month=$next_month&year=$next_year'>Next Month</a>";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $description = $_POST['description'];
    
    if (!empty($date) && !empty($description)) {
        $event = "$date|$description\n";
file_put_contents('events.txt', $event, FILE_APPEND);
        // Redirect to the same page to refresh the calendar
        header("Location: " . $_SERVER['PHP_SELF'] . "?month=$month&year=$year");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Calendar with Events</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: space-between;
            padding: 20px;
        }
        .calendar {
            width: 60%;
        }
        .event-form {
            width: 35%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="calendar">
        <h1>PHP Calendar</h1>
        <?php echo $navigation; ?>
        <?php echo $calendar; ?>
    </div>
    
    <div class="event-form">
        <h2>Add Event</h2>
        <form method="post">
            <label for="date">Date:</label><br>
            <input type="date" id="date" name="date" required><br><br>
            
            <label for="description">Description:</label><br>
            <input type="text" id="description" name="description" required><br><br>
            
            <input type="submit" value="Add Event">
        </form>
    </div>
</body>
</html>