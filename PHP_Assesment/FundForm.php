<?php
require 'user.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$success = $error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $userId = $_SESSION['user_id'];
    $reciAccNum = $_POST['receiver_account_number'];
    $transAmount = $_POST['amount'];

    $balance = new User();
    $accountBalance = $balance->getUserAccountBalance($userId);

    if ($accountBalance < $transAmount) {
        $error = "Insufficient balance";
    } else {
        $validateAccount = new User;
        $checkAcc = $validateAccount->getAccTypeUserIdFromAccountNumber($reciAccNum);

        if ($checkAcc === null) {
            $error = "Account Not Found";
        } else {
            $transFund = new User();
            $result = $transFund->transferFunds($userId, $reciAccNum, $transAmount);
            if ($result) {
                $success = "Transfer Successfully";
            } else {
                $error = " Error: Failed to transfer Money.Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Fund Transfer</title>
    <link rel="stylesheet" href="FundForm.css">
   
</head>

<body>
    <h2>Fund Transfer</h2>
    <div>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

            Receiver Account Number:
            <input type="text" id="receiver_account_number" name="receiver_account_number" required>
            <br>

            Amount:
            <input type="number" id="amount" name="amount" required>
            <br>
            <input type="submit" value="Transfer Funds">
            <div>
                <span class="success"><?php echo $success; ?></span>
                <span class="error"><?php echo $error; ?></span>

            </div>
        </form>

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