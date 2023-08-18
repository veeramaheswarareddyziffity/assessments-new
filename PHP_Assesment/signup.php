<?php

require 'user.php';


function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function isUsernameAvailable($username)
{
    $conn = DBConnection::getConnection();
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $conn->close();
    return $result->num_rows > 0;
}
function validatingUsername($username)
{
    $pattern = '/^[a-zA-Z0-9_]+$/';
    return preg_match($pattern, $username);
}

function validatingPassword($password)
{
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/';
    return preg_match($pattern, $password);
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["individual_submit"])) {
        $password =   test_input($_POST['individual_password']) ?? '';
        $username =   test_input($_POST['individual_username']) ?? '';
        $ind_balance =  test_input($_POST['individual_balance']) ?? '';
        $error_user =  $error_pass = $error_bal = $success = $error = '';
        if (empty($username) || empty($password) || empty($ind_balance)) {
            if (empty($username)) {
                $error_user = "Fill the username.";
            }
            if (empty($password)) {
                $error_pass = "Fill the password.";
            }
            if (empty($ind_balance)) {
                $error_bal = "Fill the balance";
            }
        } else {


            if (!validatingUsername($username)) {

                $error_user = "Username is not valid.";
            } elseif (!validatingPassword($password)) {

                $error_pass = "Password doesnt satisfy the norms";
            } elseif ($ind_balance < 500) {
                $error_bal = "enter a valid amount";
            } elseif (!isUsernameAvailable($username)) {
                $account_type = "single";

                $signUp = new User();

                $result = $signUp->signup($username, $password, $account_type, $ind_balance);

                if ($result) {
                    $success = 'Signup successful!.Please <a href = "login.php"> login</a> to continue. ';
                } else {
                    $error = "Error while signing up. Please try again.";
                }
            } else {
                $error_user = "Username is already taken. Please choose a different username.";
            }
        }
    }

    if (isset($_POST["joint_submit"])) {
        $username1 =  test_input($_POST['user1_username']) ?? '';
        $password1 =  test_input($_POST['user1_password']) ?? '';
        $username2 =  test_input($_POST['user2_username']) ?? '';
        $password2 =  test_input($_POST['user2_password']) ?? '';
        $joint_balance = test_input($_POST['joint_balance']) ?? '';

        $error_user1 = $error_user2 = $error_pass1 = $error_pass2 = $error_balj = $successj = $errorj = "";
        if (empty($username1) || empty($password1) || empty($username2) || empty($password2) || empty($joint_balance)) {
            if (empty($username1)) {
                $error_user1 = "Fill the first username.";
            }
            if (empty($password1)) {
                $error_pass1 = "Fill the first password.";
            }
            if (empty($username2)) {
                $error_user2 .= "Fill the second username.";
            }
            if (empty($password2)) {
                $error_pass2 .= "Fill the second password.";
            }
            if (empty($joint_balance)) {
                $error_balj = "Fill the balance";
            }
        } else {
            if (!validatingUsername($username1)) {
                $error_user1 = "First username is not valid.";
            } elseif (!validatingPassword($password1)) {
                $error_pass1 = "First password doesn't satisfy the norms.";
            } elseif (!validatingUsername($username2)) {
                $error_user2 = "Second username is not valid.";
            } elseif (!validatingPassword($password2)) {
                $error_pass2 = "Second password doesn't satisfy the norms.";
            } elseif ($joint_balance < 500) {
                $error_balj = "enter a valid amount";
            } else {
                $account_type = "joint";

                if (isUsernameAvailable($username1)) {
                    $error_user1 = " username1 are already taken. Please choose different username.";
                }
                if (isUsernameAvailable($username2)) {
                    $error_user2 = " username2 are already taken. Please choose different username.";
                } else {

                    $signUp1 = new User();
                    $result1 = $signUp1->signup($username1, $password1, $account_type, $joint_balance, $username2, $password2);


                    if ($result1) {
                        $successj = 'Joint Account Signup successful!. Please <a href="login.php">login</a> to continue.';
                    } else {
                        $errorj = "Error while signing up. Please try again.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Page</title>
    <link rel="stylesheet" href="signup.css">
    


</head>


<body>

    <h1>Signup</h1>
    <div class="signup-container">
        <div class="account-section" id="ind_account">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>"  method="POST">
                <fieldset>
                    <legend>Individual Account</legend>
                    <label for="individual_username">Username:</label>
                    <input type="text" id="individual_username" name="individual_username"  ><br>
                    <span class="error"><?php echo $error_user; ?></span>
                    <label for="individual_password">Password:</label>
                    <input type="password" id="individual_password" name="individual_password"><br>
                    <span class="error"><?php echo $error_pass; ?></span>
                    <label for="individual_balance">Balance:</label>
                    <input type="number" id="individual_balance" name="individual_balance"><br>
                    <span class="error"><?php echo $error_bal; ?></span>

                </fieldset>

                <div class="account-buttons">
                    <input type="submit" name="individual_submit" value="Create Individual Account">
                </div>
                <div class="result_container">
                    <span class="success"><?php echo $success; ?></span>
                    <span class="error"><?php echo $error; ?></span>
                </div>
            </form>
        </div>

        <div class="account-section" id="join_account">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>"  method="POST">
                <fieldset>
                    <legend>Joint Account</legend>
                    <label for="user1_username">Username1:</label>
                    <input type="text" id="user1_username" name="user1_username"><br>
                    <span class="error"><?php echo $error_user1; ?></span>
                    <label for="user1_password">Password1:</label>
                    <input type="password" id="user1_password" name="user1_password"><br>
                    <span class="error"><?php echo $error_pass1; ?></span>
                    <label for="user2_username">Username2:</label>
                    <input type="text" id="user2_username" name="user2_username"><br>
                    <span class="error"><?php echo $error_user2; ?></span>
                    <label for="user2_password">Password2:</label>
                    <input type="password" id="user2_password" name="user2_password"><br>
                    <span class="error"><?php echo $error_pass2; ?></span>
                    <label for="joint_balance">Balance:</label>
                    <input type="number" id="joint_balance" name="joint_balance"><br>
                    <span class="error"><?php echo $error_balj; ?></span>

                </fieldset>

                <div class="account-buttons">
                    <input type="submit" name="joint_submit" value="Create Joint Account">
                </div>
                <div class="result_container">
                    <span class="success"><?php echo $successj; ?></span>
                    <span class="error"><?php echo $errorj; ?></span>
                </div>
            </form><br>

        </div>

    </div>
    <div id="login"><span class="formBtn"><a href="login.php" >Login</a></span> 
    <span class="formBtn"><a href="index.php" >Index</a></span> 
    </div>

    <script type="text/javascript">
    window.history.forward();

    function noBack() {
        window.history.forward();
    }
</script>
</body>

</html>