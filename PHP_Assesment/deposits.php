
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposits</title>
    <link rel="stylesheet" href="deposits.css">
</head>

<body>


<div id="container">
    <h2>Select deposit</h2>
    <div class="button-container">
        <div><span class="formBtn"><a href="RdDeposits.php">RD Deposits</a></span></div>
        <div><span class="formBtn"><a href="FdDeposits.php">FD Deposits</a></span></div>
    </div>
</div>
<div class="button-container2">
    <div><span class="formBtn"><a href="dashboard.php">Home</a></span> </div>
    <div><span class="formBtn"><a href="logout.php">Log out</a></span> </div>

</div>
<script type="text/javascript">
    window.history.forward();

    function noBack() {
        window.history.forward();
    }
</script>
</body>

</html>