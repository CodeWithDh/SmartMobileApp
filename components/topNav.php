<!-- topnav.php -->
<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom shadow-sm px-4 py-2">
    <div class="container-fluid">
        <div class="w-100 d-flex justify-content-end pe-3">
            <form class="d-flex" style="min-width: 450px; max-width: 550px;" action="show.php" method="GET">
                <input class="form-control me-2" type="text" name="imei"
                    placeholder="🔍 Enter 15-digit IMEI..." pattern="\d{15}"
                    title="15-digit IMEI only" required
                    style="border: 2px solid #5409DA; border-radius: 8px;">
                <button class="btn btn-primary" type="submit" style="background-color: #5409DA; border: none;">
                    Search
                </button>
            </form>
        </div>
    </div>
</nav>
