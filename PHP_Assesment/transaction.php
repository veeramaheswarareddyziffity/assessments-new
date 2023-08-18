<?php
require 'user.php';

// Check if the user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$user = new User();
$accountType = $user->getAccountType($userId);

function getTransaction($userId, $accountType)
{
    $conn = DBConnection::getConnection();

    if ($accountType === 'single') {

        $query = "SELECT user_id,account_type,transaction_type,amount,description,transaction_date FROM transactions WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $transaction_history[] = $row;
        }
        $stmt->close();
    }
    if ($accountType === 'joint') {
        $query = "SELECT user1_id,user2_id FROM joint_accounts WHERE user1_id = ? OR user2_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $userId1 = $row['user1_id'];
            $userId2 = $row['user2_id'];
        }
        $stmt->close();

        $query = "SELECT user_id,account_type,transaction_type,amount,description,transaction_date FROM transactions WHERE user_id IN (?,?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $userId1, $userId2);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $transaction_history[] = $row;
        }
        $stmt->close();
    }
    return $transaction_history;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions</title>
    <link rel="stylesheet" href="transaction.css">
   
</head>

<body>
    <h2>Transaction History</h2>

    <?php
    $details = getTransaction($userId, $accountType);

    if (!empty($details)) {
        echo '<table>';
        echo '<tr><th>User ID</th><th>Transaction Type</th><th>Amount</th><th>Description</th><th>Transaction Date</th></tr>';
        foreach ($details as $transaction) {
            echo '<tr>';
            echo '<td>' . $transaction['user_id'] . '</td>';
            echo '<td>' . $transaction['transaction_type'] . '</td>';
            echo '<td>' . $transaction['amount'] . '</td>';
            echo '<td>' . $transaction['description'] . '</td>';
            echo '<td>' . $transaction['transaction_date'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo 'No transactions found.';
    }
    ?>
    <div class="button-container">
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