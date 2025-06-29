<?php include 'components/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - SmartMobileApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #ffffff;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            overflow: hidden; 
        }

        .content {
            margin-left: 250px;
            padding: 20px;
        }

        /* 🔥 Top Section */
        .top-section {
            margin-top: 70px; /* ✅ Space below nav-icon */
            border-bottom: 1.5px solid #5409DA;
            padding-bottom: 15px;
            display: flex;
            justify-content: space-evenly;
            gap: 20px;
        }

        .top-card {
            flex: 1;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-top: 4px solid #5409DA;
            transition: transform 0.3s ease;
            text-align: center;
        }

        .top-card:hover {
            transform: translateY(-5px);
        }

        .top-card h4 {
            color: #5409DA;
            margin-bottom: 10px;
        }

        .top-card a {
            background-color: #5409DA;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }

        .top-card a:hover {
            background-color: #4E71FF;
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
            color: #5409DA;
            margin: 0;
        }

@keyframes rotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.reload-btn {
    background-color: #5409DA;
    border: none;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.reload-btn:hover {
    background-color: #4E71FF;
}

/* 🔥 Only the icon spins on hover */
.reload-btn:hover .reload-icon {
    animation: rotate 0.8s linear;
    display: inline-block;
}



 /* 🔥 Table Container */
.table-container {
    background: #fff;
    border-radius: 18px 18px 0 0;
    border: 4px solid #5409DA;  /* ✅ Primary colored thick border */
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    max-height: 400px;
    overflow-y: auto;
}

/* 🔥 Table General */
.table {
    width: 100%;
    border-collapse: collapse;
}

/* 🔥 Header Styling */
.table thead {
    background-color: #5409DA;  /* ✅ Primary background */
    color: white;
}

.table thead th {
    color:white;
    padding: 14px;
    font-size: 16px;
    text-align: center;
    position: sticky;
    top: 0;
    background-color: #5409DA;
    z-index: 2;
}

.table thead th:last-child {
    border-right: none;
}

/* 🔥 Tbody Styling */
.table tbody tr {
    background-color: white;
    transition: background-color 0.3s ease;
}

.table tbody tr:hover {
    background-color: #BBFBFF;
}

.table tbody td {
    text-align: center;
    padding: 12px;
    border-bottom: 2px solid #8DD8FF;
    border-right: 2px solid #8DD8FF;
}

.table tbody td:last-child {
    border-right: none;
}

.table tbody tr:last-child td {
    border-bottom: none;
}

/* 🔥 Badge Styling */
.badge {
    padding: 6px 12px;
    border-radius: 8px;
    background-color: #4E71FF;
    color: white;
}

/* 🔥 Scrollbar Styling */
.table-container::-webkit-scrollbar {
    width: 10px;
}

.table-container::-webkit-scrollbar-track {
    background: #BBFBFF;
    border-radius: 10px;
}

.table-container::-webkit-scrollbar-thumb {
    background-color: #5409DA;
    border-radius: 10px;
    border: 2px solid #BBFBFF;
}

.table-container::-webkit-scrollbar-thumb:hover {
    background-color: #4E71FF;
}

.table-container {
    scrollbar-width: thin;
    scrollbar-color: #5409DA #BBFBFF;
}

    </style>
</head>

<body>

<div class="content">
    <!-- 🔥 Top Section -->
    <div class="top-section">
        <div class="top-card">
            <h4>Purchase</h4>
            <p>Purchase Mobile</p>
            <a href="purchaseForm.php">Go</a>
        </div>
        <div class="top-card">
            <h4>Sell</h4>
            <p>Sell Mobile</p>
            <a href="purchasedList.php">Go</a>
        </div>
        <div class="top-card">
            <h4>Return</h4>
            <p>Return Mobile</p>
            <a href="returnList.php">Go</a>
        </div>
    </div>
    <?php
include 'backend/db.php';

$sql = "SELECT * FROM purchased_mobiles ORDER BY purchase_date DESC";
$result = mysqli_query($conn, $sql);
?>


    <!-- 🔥 Bottom Section -->
    <div class="bottom-section">
        <div class="bottom-header">
            <h4>Live Data</h4>
          <button class="reload-btn" onclick="location.reload()">
            <span class="reload-icon">&#x21bb;</span> Reload
        </button>


        </div>

     <div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>IMEI</th>
                <th>Mobile Name</th>
                <th>Status</th>
                <th>Purchase Date</th>
            </tr>
        </thead>
    </table>

    <div class="table-body-scroll">
        <table class="table">
            <tbody>
                <?php 
                if (mysqli_num_rows($result) > 0) {
                    $sn = 1;
                    while($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $sn++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['IMEI']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['mobile_name']) . "</td>";
                        echo "<td><span class='badge'>" . htmlspecialchars($row['status']) . "</span></td>";
                        echo "<td>" . htmlspecialchars($row['purchase_date']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center'>No data found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>


</body>
</html>
