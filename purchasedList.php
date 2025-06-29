<?php
require 'backend/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchased Mobiles</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        table {
            border-collapse: collapse;
            width: 90%;
            margin: 30px auto;
            background-color: white;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        th, td {
            border: 2px solid #5409DA;
            text-align: center;
            padding: 12px;
        }
        th {
            background-color: #5409DA;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f7f7f7;
        }
        button {
            padding: 6px 12px;
            background-color: #4E71FF;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        button:hover {
            background-color: #5409DA;
        }
    </style>
</head>
<body>

<h2 style="text-align: center; color: #5409DA;">Purchased Mobiles List</h2>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>IMEI</th>
            <th>Mobile Name</th>
            <th>Purchase Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>

    <?php
    $sql = "SELECT * FROM purchased_mobiles WHERE status = 'purchased'";
    $result = mysqli_query($conn, $sql);
    $sn = 1;

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $sn++ . "</td>";
            echo "<td>" . htmlspecialchars($row['IMEI']) . "</td>";
            echo "<td>" . htmlspecialchars($row['mobile_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['purchase_date']) . "</td>";
            echo "<td><span style='color: green; font-weight: bold;'>" . htmlspecialchars($row['status']) . "</span></td>";
            echo "<td>
                <form method='POST' action='sellForm.php'>
                    <input type='hidden' name='imei' value='" . htmlspecialchars($row['IMEI']) . "'>
                    <button type='submit'>Sell Now</button>
                </form>
            </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No purchased mobiles found</td></tr>";
    }

    mysqli_close($conn);
    ?>

    </tbody>
</table>

</body>
</html>
