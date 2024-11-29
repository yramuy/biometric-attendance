<?php
$host = "192.168.235.39";
$dbuser = "entreplan";
$dbpassword = "Pr0duction@39";
$database = "entreplan";
$conn = mysqli_connect($host, $dbuser, $dbpassword, $database);


if (!empty($_POST['selectedDate'])) {
  $date = $_POST['selectedDate'];
} else {
  $date = date('Y-m-d'); // Correct date format
}

$employeeID = $_POST['employee'];
$myhrsQuery = "SELECT ar.employee_id,ar.punch_in_utc_time,ar.punch_out_utc_time,CONCAT(e.emp_firstname,' ',e.emp_lastname) as fullName,
                TIMEDIFF(ar.punch_out_utc_time, ar.punch_in_utc_time) AS insideOfficeTimes
                 FROM erp_attendance_record ar 
                 LEFT JOIN hs_hr_employee e ON ar.employee_id = e.emp_number
                 WHERE date(ar.punch_in_user_time) >= '$date' AND date(ar.punch_in_user_time) <= '$date'";

if (!empty($employeeID)) {
  $myhrsQuery .= " AND ar.employee_id = '$employeeID'";
}

$myhrsQuery .= " GROUP BY ar.punch_out_utc_time ORDER BY ar.employee_id ASC";

$empResult = mysqli_query($conn, $myhrsQuery);

$erp_attendance_record = mysqli_fetch_all($empResult, MYSQLI_ASSOC);
$queryCount = mysqli_num_rows($empResult);

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

  $first_punch_in = $records[0]['punch_in_utc_time'];
  $last_punch_out = end($records)['punch_out_utc_time'];

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
    'employeeID' => $records[0]['employee_id'],
    'fullName' => $records[0]['fullName'],
    'first_punch_in' => $first_punch_in,
    'last_punch_out' => $last_punch_out,
    'total_inside_office_time' => gmdate("H:i:s", $total_inside_office),
    'total_break_time' => gmdate("H:i:s", $total_break_time)
  ];
}

// Display results
$sno = 1;
foreach ($results as $employee_id => $data) {
  echo "<tr>
              
              <td>{$sno}</td>
              <td>{$data['employeeID']}</td>
              <td>{$data['fullName']}</td>
              <td>{$data['first_punch_in']}</td>
              <td>{$data['last_punch_out']}</td>
              <td>{$data['total_inside_office_time']}</td>
              <td>{$data['total_break_time']}</td>
              
            </tr>";
  $sno++;
}
