<?php include 'components/navbar.php'; ?>
<?php
require 'backend/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sold Mobiles List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: white;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .content {
            margin-left: 260px;
            transition: margin-left 0.3s ease;
        }
        .sidebar.hide ~ .content {
            margin-left: 0;
        }

        h2 {
            text-align: center;
            color: #5409DA;
            margin-bottom: 20px;
        }

        table {
            border-collapse: collapse;
            width: 90%;
            margin: auto;
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

        .status {
            font-weight: bold;
            color: blue;
        }
    </style>
</head>
<body>

<div class="content">
    <h2>Sold Mobiles (Eligible for Return)</h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>IMEI</th>
                <th>Mobile Name</th>
                <th>Sell Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>

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
                echo "<td><span class='status'>" . htmlspecialchars($row['status']) . "</span></td>";
                echo "<td>
                    <form method='POST' action='returnForm.php'>
                        <input type='hidden' name='imei' value='" . htmlspecialchars($row['IMEI']) . "'>
                        <button type='submit'>Return Now</button>
                    </form>
                </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6' style='text-align:center;'>No sold mobiles found</td></tr>";
        }

        mysqli_close($conn);
        ?>

        </tbody>
    </table>
</div>

</body>
</html>
