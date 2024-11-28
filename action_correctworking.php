<?php
$erp_attendance_record = array(
    array('employee_id' => '163','punch_in_utc_time' => '2024-11-26 09:12:12','punch_out_utc_time' => '2024-11-26 09:44:59','fullName' => 'Mounish Gangula'),
    array('employee_id' => '54','punch_in_utc_time' => '2024-11-26 09:55:13','punch_out_utc_time' => '2024-11-26 13:23:03','fullName' => 'Ramu Yerramsetti'),
    array('employee_id' => '163','punch_in_utc_time' => '2024-11-26 09:49:33','punch_out_utc_time' => '2024-11-26 13:25:52','fullName' => 'Mounish Gangula'),
    array('employee_id' => '163','punch_in_utc_time' => '2024-11-26 14:33:54','punch_out_utc_time' => '2024-11-26 14:37:23','fullName' => 'Mounish Gangula'),
    array('employee_id' => '54','punch_in_utc_time' => '2024-11-26 13:23:19','punch_out_utc_time' => '2024-11-26 16:30:16','fullName' => 'Ramu Yerramsetti'),
    array('employee_id' => '163','punch_in_utc_time' => '2024-11-26 14:55:43','punch_out_utc_time' => '2024-11-26 19:03:34','fullName' => 'Mounish Gangula'),
    array('employee_id' => '54','punch_in_utc_time' => '2024-11-26 17:36:56','punch_out_utc_time' => '2024-11-26 19:03:56','fullName' => 'Ramu Yerramsetti')
);

// Group records by employee
$grouped_records = [];
foreach ($erp_attendance_record as $record) {
    $grouped_records[$record['employee_id']][] = $record;
}

$results = [];

foreach ($grouped_records as $employee_id => $records) {
    // Sort records by punch_in time
    usort($records, function ($a, $b) {
        return strtotime($a['punch_in_utc_time']) - strtotime($b['punch_in_utc_time']);
    });

    $total_inside_office = 0;
    $total_break_time = 0;
    $previous_out_time = null;

    foreach ($records as $record) {
        $punch_in_time = strtotime($record['punch_in_utc_time']);
        $punch_out_time = strtotime($record['punch_out_utc_time']);

        // Calculate inside office time
        $total_inside_office += $punch_out_time - $punch_in_time;

        // Calculate break time
        if ($previous_out_time !== null) {
            $break_time = $punch_in_time - $previous_out_time;
            if ($break_time > 0) {
                $total_break_time += $break_time;
            }
        }

        $previous_out_time = $punch_out_time;
    }

    // Format the results
    $results[$employee_id] = [
        'fullName' => $records[0]['fullName'],
        'total_inside_office_time' => gmdate("H:i:s", $total_inside_office),
        'total_break_time' => gmdate("H:i:s", $total_break_time)
    ];
}

// Display results
foreach ($results as $employee_id => $data) {
    echo "Employee: " . $data['fullName'] . "\n";
    echo "Total Inside Office Time: " . $data['total_inside_office_time'] . "\n";
    echo "Total Break Time: " . $data['total_break_time'] . "\n";
    echo str_repeat("-", 30) . "\n";
}
