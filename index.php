<?php
$host = "192.168.235.39";
$dbuser = "entreplan";
$dbpassword = "Pr0duction@39";
$database = "entreplan";
$conn = mysqli_connect($host, $dbuser, $dbpassword, $database);

$empSql = "SELECT * FROM hs_hr_employee WHERE termination_id IS NULL";
$empResult = mysqli_query($conn, $empSql);
$count = mysqli_num_rows($empResult);
// if (mysqli_num_rows($empResult) > 0) {

//     while ($row = mysqli_fetch_assoc($empResult)) {
//         echo $row['emp_number'].'\n';
//     }
// }


// $data = [
//   ["state" => "PUNCHED OUT", "punchin" => "2024-11-19 14:38:13", "punchout" => "2024-11-19 15:08:08"],
//   ["state" => "PUNCHED OUT", "punchin" => "2024-11-19 15:09:10", "punchout" => "2024-11-19 16:37:46"],
//   ["state" => "PUNCHED OUT", "punchin" => "2024-11-19 17:49:10", "punchout" => "2024-11-19 18:26:58"],
//   ["state" => "PUNCHED OUT", "punchin" => "2024-11-19 18:27:07", "punchout" => "2024-11-19 19:00:47"]
// ];

// $totalInside = 0; // Total inside office time in seconds
// $totalOutside = 0; // Total outside office time in seconds

// for ($i = 0; $i < count($data); $i++) {
//   $currentPunchIn = strtotime($data[$i]['punchin']);
//   $currentPunchOut = strtotime($data[$i]['punchout']);

//   // Calculate inside time
//   $totalInside += $currentPunchOut - $currentPunchIn;

//   // Calculate outside time if there's a next record
//   if ($i < count($data) - 1) {
//     $nextPunchIn = strtotime($data[$i + 1]['punchin']);
//     $totalOutside += $nextPunchIn - $currentPunchOut;
//   }
// }

// // Convert seconds to hours, minutes, and seconds
// function formatTime($seconds)
// {
//   $hours = floor($seconds / 3600);
//   $minutes = floor(($seconds % 3600) / 60);
//   $seconds = $seconds % 60;
//   return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
// }

// echo "Total Inside Office: " . formatTime($totalInside) . PHP_EOL;
// echo "Total Outside Office: " . formatTime($totalOutside) . PHP_EOL;





?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Bootstrap Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <!-- CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

  <!-- JavaScript -->
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

</head>

<body>

  <div class="container">
    <h2>Biometric Summary</h2>
    <form class="form-inline" action="/action_page.php">
      <div class="form-group">
        <label for="email">Date:</label>
        <input type="date" class="form-control" id="date" placeholder="Enter email" name="date" onchange="handleOnchange()">
      </div>
      <div class="form-group">
        <label for="email">Employee:</label>
        <select name="employee" id="employee" class="form-control" onchange="handleOnchange()">
          <option value="">--Select--</option>
          <?php while ($row = mysqli_fetch_assoc($empResult)) { ?>
            <option value="<?php echo $row['emp_number']; ?>"><?php echo $row['emp_lastname'] . ' ' . $row['emp_firstname']; ?></option>
          <?php } ?>
        </select>
      </div>
    </form>

    <div class="row mb-4" id="summaryTable"><br />
      <table class="table table-bordered mb-4" id="biometricTable">
        <thead>
          <tr>
            <th>Sno</th>
            <th>Employee Number</th>
            <th>Employee Name</th>
            <th>First Punch-In</th>
            <th>Last Punch-Out</th>
            <th>Inside Office Hours</th>
            <th>Break Time</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
  </div>

</body>

</html>
<script>
  let tr = '<tr><td colspan="7"><center><h4>No data found!</h4></center></td></tr>';
  $('#summaryTable tbody').append(tr);
  function handleOnchange(emp, date) {
    var empVal = $('#employee').val();
    var dateVal = $('#date').val();
    $('#summaryTable tbody').html('');

    // Get current date
    var today = new Date();

    // Format the date as YYYY-MM-DD
    var formattedDate = today.getFullYear() + '-' +
      ('0' + (today.getMonth() + 1)).slice(-2) + '-' +
      ('0' + today.getDate()).slice(-2);

    // Output the date
    if (dateVal >= formattedDate) {
      alert('Do not choose a current or future date.');
      $('#date').val('');
      $('#employee').val('');
      $('#summaryTable tbody').append(tr);
      return false;
    }

    if (dateVal == '') {
      alert('Please select a date');
      $('#employee').val('');
      $('#date').val('');
      $('#summaryTable tbody').append(tr);
      return false;
    }

    if (empVal != "" || dateVal != "") {
      $.ajax({
        type: "POST",
        url: "action.php",
        data: {
          'employee': empVal,
          'selectedDate': dateVal
        },
        // dataType: 'json',
        success: function(response) {
          // var decodeHtml = JSON.parse(response);
          console.log(response)
          if(response != ""){
            $('#summaryTable tbody').append(response);
          } else {
            $('#summaryTable tbody').append(tr);
          }
          
          console.log(response)
        }
      });
    }


  }

  $('#biometricTable').DataTable({
    dom: 'Bfrtip',
    buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
  });
</script>