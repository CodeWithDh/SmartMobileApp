<?php include 'components/navbar.php'; ?>
<?php require 'backend/db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchased Mobiles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #fff;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            overflow: hidden;
            transition: margin-left 0.3s ease;
        }

        /* 🔥 Content shifts with Navbar */
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .sidebar.hide ~ .content {
            margin-left: 0;
        }

        /* 🔥 Top Section */
        .top-section {
            margin-top: 70px;
            border-bottom: 1.5px solid #ffffff;
            padding-bottom: 15px;
            display: flex;
            justify-content: space-evenly;
            gap: 20px;
        }

        .top-card {
            flex: 1;
            background: #BBFBFF;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-top: 4px solid #ffffff;
            transition: transform 0.3s ease;
            text-align: center;
        }

        .top-card:hover {
            transform: translateY(-5px);
        }

        .top-card h4 {
            color: #5409DA;
        }

        .top-card a {
            background-color: #4E71FF;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
        }

        .top-card a:hover {
            background-color: #5409DA;
        }

        /* 🔥 Bottom Section */
        .bottom-section {
            margin-top: 40px;
        }

        .bottom-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .bottom-header h4 {
            color: white;
            margin: 0;
        }

        /* 🔥 Table */
        table {
            border-collapse: collapse;
            width: 95%;
            margin: 0 auto;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
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
            background-color: #f4f9ff;
        }

        tr:hover {
            background-color: #e0f0ff;
        }

        /* 🔥 Buttons */
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

<div class="content">
    <h2 style="text-align: center; color: white;">Purchased Mobiles List</h2>

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
</div>

</body>
</html>
