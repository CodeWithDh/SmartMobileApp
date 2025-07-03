<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔥 Authentication check
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

// 🔥 Logout check
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<style>
    /* Sidebar */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 250px;
        height: 100vh;
        background-color: #5409DA;
        color: white;
        padding: 20px;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease;
        z-index: 1000;
    }

    .sidebar.hide {
        transform: translateX(-100%);
    }

    .sidebar .nav-logo {
        display: flex;
        margin-top: 40px;
        margin-right: 30px;
        height: 120px;
        align-items: center;
        justify-content: center;
        
    }

    .sidebar .nav-logo img {
        width: 120px;
    }

    .sidebar .nav {
        margin-top: 50px;
        flex-grow: 1;
        display: flex;  
        flex-direction: column;
        gap: 35px;
    }

    .sidebar .nav .nav-link {
        color: white;
        text-decoration: none;
        transition: color 0.3s ease;
        font-size: 1.05rem;
    }

    .sidebar .nav .nav-link:hover {
        color: #BBFBFF;
    }

    .sidebar .logout {
        font-size: 1.3rem;
        font-weight: bold;
        
    }

    /* Toggle Button */
    .menu-toggle {
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1100;
        background-color: #5409DA;
        color: white;
        border: none;
        padding: 8px 10px;
        border-radius: 8px;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        transition: background-color 0.3s ease;
    }

    .menu-toggle:hover {
        background-color: #4E71FF;
    }

    .menu-icon {
        font-size: 18px;
    }

    /* Content shift */
    .content {
        margin-left: 250px;
        transition: margin-left 0.3s ease;
    }

    .sidebar.hide ~ .content {
        margin-left: 0;
    }
    .nav-logo img {
        width: 200px;
        height: 90px;
        cursor: pointer;
    }
</style>




<!-- 🔥 Navbar HTML -->
<div class="sidebar" id="sidebar">
    <div class="nav-logo">
        <img src="assets/nav-logo.png" alt="Logo">
    </div>

    <div class="nav">
        <a class="nav-link" href="dashboard.php">Dashboard</a>
        <a class="nav-link" href="purchasedList.php">Purchased Mobiles</a>
        <a class="nav-link" href="returnList.php">Sold Mobiles</a>
        <a class="nav-link" href="returnedList.php">Returned Mobiles</a>
        <div class="logout">
            <a class="nav-link" href="?logout=true">Logout</a>
        </div>
    </div>

    
</div>

<!-- 🔥 Toggle Button -->
<button class="menu-toggle" onclick="toggleSidebar()">
    <span class="menu-icon">&#9776;</span>
</button>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('hide');
    }
</script>
