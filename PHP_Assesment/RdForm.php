<?php
require 'user.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION['user_id'];

    $monthlyAmount = $_POST['monthly_amount'];
    $durationInMonths = $_POST['duration_in_months'];

    $balance = new User();
    $accountBalance = $balance->getUserAccountBalance($userId);
    if ($accountBalance < $monthlyAmount) {
        $error = "Insufficient balance";
    } else {
        $recurringDeposit = new User();

        $result = $recurringDeposit->createRecurringDeposit($userId, $monthlyAmount, $durationInMonths);

        if ($result) {
            $intrest = new User();

            $intrestRateAmount = $intrest->calculateRdIntrestRate($monthlyAmount, $durationInMonths);

            $intrestAmount = $intrestRateAmount[0];
            $intrestRate = $intrestRateAmount[1];
            $success = "Recurring Deposit Account Created Successfully";
        } else {
            $error = " Error: Failed to Create Recurring Deposit Acccount.Please try again.";
            // echo $result;
        }
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Create Recurring Deposit Account</title>
    <link rel="stylesheet" href="RdForm.css">
    
</head>

<body>
    <h1>Create Recurring Deposit</h1>
    <div>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <label for="monthly_amount">Monthly Deposit Amount:</label>
            <input type="number" name="monthly_amount" id="monthly_amount" required>
            <label for="duration_in_months">Duration (in months):</label>
            <input type="number" name="duration_in_months" id="duration_in_months" required>
            <input type="submit" value="Create RD Account">
            <span class="success"><?php echo $success; ?></span>
            <span class="error"><?php echo $error; ?></span>

        </form>
        <div>
            <div class="intrest_result">
                <?php
                if ($result) {

                    echo "<h3>Based on your data</h3>";
                    echo "Intrest Rate : " . $intrestRate . "</p><p>" . "Intrest Amount : " . $intrestAmount . "</p>";
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