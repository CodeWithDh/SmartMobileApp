<!-- topnav.php -->
<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom shadow-sm px-4 py-2">
    <div class="container-fluid">
        <form class="d-flex ms-auto" action="show.php" method="GET">
            <input class="form-control me-2" type="text" name="imei" placeholder="Enter IMEI..." pattern="\d{15}" title="15-digit IMEI only" required>
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
    </div>
</nav>
