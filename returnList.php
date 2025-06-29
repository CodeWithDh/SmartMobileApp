<?php
require 'backend/db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sold Mobiles List</title>
</head>
<body>

<h2 style="text-align: center; color:#5409DA;">Sold Mobiles (Eligible for Return)</h2>

<table border="1" cellspacing="0" cellpadding="10" align="center">
    <tr>
        <th>#</th>
        <th>IMEI</th>
        <th>Mobile Name</th>
        <th>Sell Date</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

<?php
$sql = "SELECT * FROM purchased_mobiles WHERE status = 'sold'";
$result = mysqli_query($conn, $sql);
$sn = 1;

if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $sn++ . "</td>";
        echo "<td>" . htmlspecialchars($row['IMEI']) . "</td>";
        echo "<td>" . htmlspecialchars($row['mobile_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['sell_date']) . "</td>";
        echo "<td style='color:blue;font-weight:bold;'>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>
            <form method='POST' action='returnForm.php'>
                <input type='hidden' name='imei' value='" . htmlspecialchars($row['IMEI']) . "'>
                <button type='submit'>Return Now</button>
            </form>
        </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No sold mobiles found</td></tr>";
}

mysqli_close($conn);
?>
</table>

</body>
</html>
