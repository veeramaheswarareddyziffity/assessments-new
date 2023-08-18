<?php
require 'user.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $success = $error = "";
    $userId = $_SESSION['user_id'];

    $amount = $_POST['amount'];
    $durationInMonths = $_POST['duration'];

    $balance = new User();
    $accountBalance = $balance->getUserAccountBalance($userId);
    if ($accountBalance < $amount) {
        $error = "Insufficient balance";
    } else {

        $fixedDeposit = new User();

        $result = $fixedDeposit->createFixedDeposit($userId, $amount, $durationInMonths);

        // echo $result;
        if ($result) {
            $fdIntrest = new User();


            $intrestRateAmount = $fdIntrest->calculateFdInterestRate($amount, $durationInMonths);

            $intrestAmount = $intrestRateAmount[0];
            $intrestRate = $intrestRateAmount[1];
            $success = "Fixed Deposit Account Created Successfully";
        } else {
            $error = " Error: Failed to Create Fixed Deposit Acccount.Please try again.";
            // echo $result;
        }
    }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="FdForm.css">
    
</head>

<body>
    <div class="container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="amount">Deposit Amount:</label>
            <input type="number" name="amount" id="amount" required>
            <label for="duration">Duration (in months):</label>
            <input type="number" name="duration" id="duration" required>
            <input type="submit" value="Create FD Account">
            <span class="success"><?php echo $success; ?></span>
            <span class="error"><?php echo $error; ?></span>
        </form>
        <div>
            <div class="intrest_result">
                <?php
                if ($result) {

                    echo "<h3>Based on your data</h3>";
                    echo "Intrest Rate : " . $intrestRate . "</p><p>" . "Intrest Amount : " .  $intrestAmount . "</p>";
                }
                ?>
            </div>
        </div>
        <div class="button-container">
            <div><span class="formBtn"><a href="dashboard.php">Home</a></span> </div>
            <div><span class="formBtn"><a href="logout.php">Log out</a></span> </div>

        </div>
    </div>

    <script type="text/javascript">
        window.history.forward();

        function noBack() {
            window.history.forward();
        }
    </script>

</body>

</html>