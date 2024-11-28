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

$employee = $_POST['employee'];

$query = "SELECT * FROM hs_hr_employee WHERE termination_id IS NULL";
$result1 = mysqli_query($conn, $query);

$count = mysqli_num_rows($result1);
$html = "";
$output1 = array();
$index = 0;
// while ($emp = mysqli_fetch_assoc($result)) {
// $empNumber = $emp['emp_number'];
// $fullName = $emp['emp_firstname'] . ' ' . $emp['emp_lastname'];
$myhrsQuery = "SELECT ar.*,CONCAT(e.emp_firstname,' ',e.emp_lastname) as fullName
                 FROM erp_attendance_record ar 
                 LEFT JOIN hs_hr_employee e ON ar.employee_id = e.emp_number
                 WHERE date(ar.punch_in_user_time) = '$date'";

// $myhrsQuery .= " AND ar.employee_id = $empNumber ORDER BY ar.id ASC";

$empResult = mysqli_query($conn, $myhrsQuery);

$erp_attendance_record = mysqli_fetch_all($empResult, MYSQLI_ASSOC);

print_r($erp_attendance_record);// Fetch all rows as an array


$result = [];

foreach ($erp_attendance_record as $record) {
  $employee_id = $record['employee_id'];
  $punch_in = new DateTime($record['punch_in_utc_time']);
  $punch_out = new DateTime($record['punch_out_utc_time']);
  $duration = $punch_out->diff($punch_in);

  if (!isset($result[$employee_id])) {
    $result[$employee_id] = [
      'total_office_seconds' => 0,
      'break_seconds' => 0,
      'last_punch_out' => null,
      'first_punch_in' => $record['punch_in_utc_time'],
      'last_punch_out_final' => $record['punch_out_utc_time'],
      'employee_id' => $record['employee_id'],
      'emp_name' => $record['fullName']
    ];
  } else {
    // Update first punch-in time if the current punch-in is earlier
    if (new DateTime($record['punch_in_utc_time']) < new DateTime($result[$employee_id]['first_punch_in'])) {
      $result[$employee_id]['first_punch_in'] = $record['punch_in_utc_time'];
    }

    // Update last punch-out time if the current punch-out is later
    if (new DateTime($record['punch_out_utc_time']) > new DateTime($result[$employee_id]['last_punch_out_final'])) {
      $result[$employee_id]['last_punch_out_final'] = $record['punch_out_utc_time'];
    }
  }

  // Add to total office time
  $result[$employee_id]['total_office_seconds'] += $duration->h * 3600 + $duration->i * 60 + $duration->s;

  // Calculate break time if applicable
  if ($result[$employee_id]['last_punch_out'] !== null) {
    $last_punch_out = new DateTime($result[$employee_id]['last_punch_out']);
    $break_duration = $punch_in->diff($last_punch_out);
    $result[$employee_id]['break_seconds'] += $break_duration->h * 3600 + $break_duration->i * 60 + $break_duration->s;
  }

  $result[$employee_id]['last_punch_out'] = $record['punch_out_utc_time'];
}


foreach ($result as $employee_id => $data) {
  // Calculate total office time
  $total_office_hours = floor($data['total_office_seconds'] / 3600);
  $total_office_minutes = floor(($data['total_office_seconds'] % 3600) / 60);
  $total_office_seconds = $data['total_office_seconds'] % 60;

  // Calculate break time
  $break_hours = floor($data['break_seconds'] / 3600);
  $break_minutes = floor(($data['break_seconds'] % 3600) / 60);
  $break_seconds = $data['break_seconds'] % 60;

  // Calculate inside office hours (subtract break time from total office time)
  $inside_office_seconds = $data['total_office_seconds'] - $data['break_seconds'];
  $inside_office_hours = floor($inside_office_seconds / 3600);
  $inside_office_minutes = floor(($inside_office_seconds % 3600) / 60);
  $inside_office_seconds = $inside_office_seconds % 60;

  $totalOfficeHours = $total_office_hours . ':' . $total_office_minutes . ':' . $total_office_seconds;
  $breaks = $break_hours . ':' . $break_minutes . ':' . $break_seconds;
  $insideOfficeHours = $inside_office_hours . ':' . $inside_office_minutes . ':' . $inside_office_seconds;
  // <td>{$totalOfficeHours}</td>
  echo "<tr>
            <td>{$data['employee_id']}</td>
            <td>{$data['emp_name']}</td>
            <td>{$data['first_punch_in']}</td>
            <td>{$data['last_punch_out_final']}</td>
            <td>{$totalOfficeHours}</td>
            <td>{$insideOfficeHours}</td>
            <td>{$breaks}</td>
            
          </tr>";
}

