<?php

// Sample punch data
$punchData = [
   ['id' => 33, 'empId' => 129, 'name' => 'I.Eswara Rao', 'dateTime' => '2024-10-17 09:52:21', 'type' => 'Punch IN', 'location' => '2F LIFT'],
    ['id' => 64, 'empId' => 129, 'name' => 'I.Eswara Rao', 'dateTime' => '2024-10-17 11:57:58', 'type' => 'Punch Out', 'location' => '2F Main'],
    ['id' => 65, 'empId' => 129, 'name' => 'I.Eswara Rao', 'dateTime' => '2024-10-17 12:08:32', 'type' => 'Punch IN', 'location' => '1F Main'],
    ['id' => 68, 'empId' => 129, 'name' => 'I.Eswara Rao', 'dateTime' => '2024-10-17 12:29:10', 'type' => 'Punch Out', 'location' => '1F Main'],
    ['id' => 69, 'empId' => 129, 'name' => 'I.Eswara Rao', 'dateTime' => '2024-10-17 12:29:38', 'type' => 'Punch IN', 'location' => '2F Main'],
    ['id' => 137, 'empId' => 129, 'name' => 'I.Eswara Rao', 'dateTime' => '2024-10-17 14:48:03', 'type' => 'Punch Out', 'location' => '2F Main'],
    ['id' => 138, 'empId' => 129, 'name' => 'I.Eswara Rao', 'dateTime' => '2024-10-17 14:51:24', 'type' => 'Punch IN', 'location' => '1F Main']
];

// Function to calculate time difference in minutes
function getTimeDifference($startTime, $endTime) {
    $start = new DateTime($startTime);
    $end = new DateTime($endTime);
    $interval = $start->diff($end);
    return ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i; // return difference in minutes
}

// Function to process punches and ignore duplicate entries
function processPunchData($punchData) {
    $processedPunches = [];
    
    foreach ($punchData as $record) {
        // Only add the punch if it is the first punch in the sequence or differs from the previous punch type
        if (empty($processedPunches) || 
           ($record['type'] == 'Punch IN' && end($processedPunches)['type'] == 'Punch Out') ||
           ($record['type'] == 'Punch Out' && end($processedPunches)['type'] == 'Punch IN')) {
            $processedPunches[] = $record;
        }
    }

    return $processedPunches;
}

// Function to calculate total time spent inside and outside the office
function calculateTimeSpent($punches) {
    $totalTimeInside = 0;
    $totalTimeOutside = 0;

    for ($i = 0; $i < count($punches) - 1; $i++) {
        $currentPunch = $punches[$i];
        $nextPunch = $punches[$i + 1];

        $timeDiff = getTimeDifference($currentPunch['dateTime'], $nextPunch['dateTime']);

        // If current punch is Punch IN and next punch is Punch Out, add to time spent inside
        if ($currentPunch['type'] == 'Punch IN' && $nextPunch['type'] == 'Punch Out') {
            $totalTimeInside += $timeDiff;
        }
        // If current punch is Punch Out and next punch is Punch IN, add to time spent outside
        else if ($currentPunch['type'] == 'Punch Out' && $nextPunch['type'] == 'Punch IN') {
            $totalTimeOutside += $timeDiff;
        }
    }

    return ['totalTimeInside' => $totalTimeInside, 'totalTimeOutside' => $totalTimeOutside];
}

// Step 1: Process the raw punch data
$processedPunches = processPunchData($punchData);

// Step 2: Calculate time spent inside and outside the office
$timeSpent = calculateTimeSpent($processedPunches);

// Output the results
echo "Total time spent inside the office: " . $timeSpent['totalTimeInside'] . " minutes\n";
echo "Total time spent outside the office: " . $timeSpent['totalTimeOutside'] . " minutes\n";

?>
