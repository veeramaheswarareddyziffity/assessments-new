<?php
session_start();
require 'user.php';


function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = test_input($_POST["username"]) ?? '';
    $password = test_input($_POST["password"]) ?? '';
    $error_user = $error_pass = "";
    if (empty($username) || empty($password)) {
        if (empty($username)) {
            $error_user = "fill the username";
        }
        if (empty($password)) {
            $error_pass = "fill the password";
        }
    } else {

        $login = new User();

        $userId = $login->login($username, $password);

        if ($userId) {
            $_SESSION["user_id"] = $userId;
            header("Location: dashboard.php");
        } else {
            $error_pass = "Invalid username or password.";
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
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
    


</head>

<body>
    <div class="login-container">
        <h1>Login</h1>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username"><br>
            <span class="error"><?php echo $error_user; ?></span><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password">
            <span class="error"><?php echo $error_pass; ?></span>
            <input type="submit" value="Login">
        </form>
    </div>
    <div id="login"><span class="formBtn"><a href="signup.php">SignUp</a></span>
    <span class="formBtn"><a href="index.php" >Index</a></span>  </div>
    <script type="text/javascript">
        window.history.forward();

        function noBack() {
            window.history.forward();
        }
    </script>
</body>

</html>