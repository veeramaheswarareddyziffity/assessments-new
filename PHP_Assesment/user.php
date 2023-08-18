<?php
require '/var/www/html/assesment/PHP_Assesment/DbConnection.php';
// echo "reddy";
class User{

    //signup process 

    public function signup($username,$password,$accountType,$balance,$username2 = null ,$password2 = null){
        if($accountType === 'single'){
            $userId = $this->createUser($username,$password,$accountType);
            $this->createSingleAccount($userId,$balance);
            return true;
        }
        if($accountType === 'joint'){
            $userId1 = $this->createUser($username,$password,$accountType);
            $userId2 = $this->createUser($username2,$password2,$accountType);
            $this->createJointAccount($userId1,$userId2,$balance);
            return true;
        }

        return null;
    }


    //creating users

    private function createUser($username,$password,$accountType){
        $conn = DBConnection::getConnection();
    
        $encryptedPassword = password_hash($password, PASSWORD_BCRYPT);
    
        $stmt = $conn->prepare("INSERT INTO users(username, password, account_type) VALUES (?,?,?)");
        $stmt->bind_param("sss", $username, $encryptedPassword, $accountType);
        $stmt->execute();
        $stmt->close();
        
        $userId = $this->getUserIdByUsername($username);
        return $userId;
    }

    //getting userId by username

    public function getUserIdByUsername($username){
        $conn = DBConnection::getConnection();

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $userId = $row['id'];

        $stmt->close();

        return $userId;
    }

    //getting accountType by user id
    public function getAccountType($userId){
        $conn = DBConnection::getConnection();
        $stmt = $conn->prepare("SELECT account_type FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            return $row['account_type'];
        } else {
            return null; 
        }
        $stmt->close();
    }

    public function getUserAccountNumber($userId){
        $conn = DBConnection::getConnection();

        $accountType = $this->getAccountType($userId);
        if($accountType === 'single'){
            $stmt = $conn->prepare("SELECT account_number FROM ind_accounts WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $accountNumber = $row['account_number'];
               
            }
            $stmt->close();
            return $accountNumber;
        }
        if($accountType === 'joint'){
            $stmt = $conn->prepare("SELECT account_number FROM joint_accounts WHERE user1_id = ? OR user2_id = ?");
            $stmt->bind_param("ii", $userId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                
                $accountNumber = $row['account_number'];
            
            }
            $stmt->close();
            return $accountNumber;
        }
        
    }


    public function getUserAccountBalance($userId){
        $conn = DBConnection::getConnection();

        $accountType = $this->getAccountType($userId);
        if($accountType === 'single'){
            $stmt = $conn->prepare("SELECT balance FROM ind_accounts WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $balance = $row['balance'];
               
            }
            $stmt->close();
            return $balance;
        }
        if($accountType === 'joint'){
            $stmt = $conn->prepare("SELECT balance FROM joint_accounts WHERE user1_id = ? OR user2_id = ?");
            $stmt->bind_param("ii", $userId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                
                $balance = $row['balance'];
            
            }
            $stmt->close();
            return $balance;
        }
        
    }
    

    //login process

    public function login($username,$password){
        $conn = DBConnection::getConnection();
        $stmt = $conn->prepare("SELECT id,password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $userId = $row['id'];
        $hashedPassword = $row['password'];

        if (password_verify($password, $hashedPassword)) {
            return $userId;
        }
    }

    return false;

    }


    //creating individual account

    private function createSingleAccount($userId,$balance){
        $conn = DBConnection::getConnection();

        $accountNumber = "IND" . time(); //unique account number

        $stmt = $conn->prepare("INSERT INTO ind_accounts (account_number,user_id,balance) VALUES (?,?,?)");

        $stmt->bind_param("sid",$accountNumber,$userId,$balance);
        $stmt->execute();
        $stmt->close();
        $this->depositMoney($userId,$balance);
    }


    private function createJointAccount($userId1,$userId2,$balance){
        $conn = DBConnection::getConnection();

        $accountNumber = "JNT" . time(); // unique account number

        $stmt = $conn->prepare("INSERT INTO joint_accounts (account_number,user1_id,user2_id,balance) VALUES (?,?,?,?)");

        $stmt->bind_param("siid",$accountNumber,$userId1,$userId2,$balance);

        $stmt->execute();
        $stmt->close();
        $this->depositMoney($userId1,$balance);

    }


    //depositing money

    public function depositMoney($userId,$depositAmount){
         
        // echo "enter the function"; 
        $conn = DBConnection::getConnection();
        // echo "enter the conn"; 

        $accountType = $this->getAccountType($userId);

        // echo "enter the acounttype"; 
        if ($depositAmount <= 0) {

            $conn->close();
            return false;
        }

        if($accountType === 'single'){
            
            $stmt = $conn->prepare("UPDATE ind_accounts SET balance = balance + ? WHERE user_id = ?");
            $stmt->bind_param("di",$depositAmount,$userId);
           
        }
        elseif($accountType === "joint"){
            $stmt = $conn->prepare("UPDATE joint_accounts SET balance = balance + ? WHERE user1_id = ? OR user2_id = ?");
            $stmt->bind_param("dii",$depositAmount,$userId,$userId);
        }
        else{
            $conn->close();
            return false;
        }

        if($stmt->execute()){
            $transactionType = 'deposite';
            $description = "Deposited +$depositAmount";

            $trans_stmt = $conn->prepare("INSERT INTO transactions(user_id, account_type, transaction_type, amount, description) VALUES (?, ?, ?, ?, ?)");
            $trans_stmt->bind_param("issds", $userId, $accountType, $transactionType, $depositAmount, $description);
            $trans_stmt->execute();
            $trans_stmt->close();
            $conn->close();

            return true;
        }
        else{
            $conn->close();
            return false;
        }


    }


    public function withdrawMoney($userId,$withdrawAmount){
        // echo $withdraw_amount;
        $conn = DBConnection::getConnection();
     
        $accountType = $this->getAccountType($userId);

        // if ($withdrawAmount < 500 ) {

        //     $conn->close();
        //     return false;
        // }
       
        if($accountType === 'single'){
            // echo $withdraw_amount;
            $stmt = $conn->prepare("UPDATE ind_accounts SET balance = balance - ? WHERE user_id = ?");
            // echo $account_type;
            $stmt->bind_param("di",$withdrawAmount,$userId);
           
        }
        elseif($accountType === "joint"){
           
            $stmt = $conn->prepare("UPDATE joint_accounts SET balance = balance - ? WHERE user1_id = ? OR user2_id = ?");
            $stmt->bind_param("dii",$withdrawAmount,$userId,$userId);
            // echo $withdraw_amount;
        }
        else{
            $conn->close();
            return false;
        }
        // echo $withdraw_amount;
        if($stmt->execute()){
            // echo $withdraw_amount;
            $transactionType = 'withdraw';
            $description = "Withdraw -$withdrawAmount";
           

            $trans_stmt = $conn->prepare("INSERT INTO transactions(user_id, account_type, transaction_type, amount, description) VALUES (?, ?, ?, ?, ?)");
            $trans_stmt->bind_param("issds", $userId, $accountType, $transactionType, $withdrawAmount, $description);
            $trans_stmt->execute();
            $trans_stmt->close();
            $conn->close();

            return true;
        }
        
            $conn->close();
            return false;
        

    }

    //calculate intrest for fixed deposite
    public function calculateFdInterestRate($amount, $durationInMonths) {
       
        $intrestRatePerAnnum = 0.0;
    
        if ($durationInMonths > 0 && $durationInMonths <= 6) {
            $intrestRatePerAnnum = 3.0;
        } 
        elseif ($durationInMonths > 6 && $durationInMonths <= 12) {
            $intrestRatePerAnnum = 4.0;
        } 
        elseif ($durationInMonths > 12 && $durationInMonths <= 24) {
            $intrestRatePerAnnum = 5.0;
        } 
        elseif ($durationInMonths > 24) {
            $intrestRatePerAnnum= 6.0;
        }
    
        $durationInYears = $durationInMonths / 12;
        $intrestAmount = ($amount * $intrestRatePerAnnum * $durationInYears) / 100;
    
    return [$intrestAmount,floatval($intrestRatePerAnnum)];
    }
    

    // calculate intrest for recurring deposite
    public function calculateRdIntrestRate($monthlyAmount,$durationInMonths){

        $intrestRatePerAnnum = 0.0;

        if ($durationInMonths > 0 && $durationInMonths <= 6) {
            $intrestRatePerAnnum = 3.0;
        } 
        elseif ($durationInMonths > 6 && $durationInMonths <= 12) {
            $intrestRatePerAnnum = 4.0;
        } 
        elseif ($durationInMonths > 12 && $durationInMonths <= 24) {
            $intrestRatePerAnnum = 5.0;
        } 
        elseif ($durationInMonths > 24) {
            $intrestRatePerAnnum= 6.0;
        }


        $intrestAmount = ($monthlyAmount/12) * ($intrestRatePerAnnum/100 )* (($durationInMonths * ($durationInMonths + 1))/2);

        return [$intrestAmount,floatval($intrestRatePerAnnum)] ;
        
    }

    // creating fixed deposit

    public function createFixedDeposit($userId,$amount,$durationInMonths){
        $conn = DBConnection::getConnection();

        if ($amount <= 0 || $durationInMonths <= 0) {
            
            // echo "fialed1";
            $conn->close();
            return false;
        }
        $userBalance = $this->getUserAccountBalance($userId);

        $accountType = $this->getAccountType($userId);

        if( $userBalance < $amount ){
            $conn->close();
            // echo "fialed2";
            return false;
        }

        $intrestRate = $this->calculateFdInterestRate($amount,$durationInMonths);
         
        $intrestAmount = $intrestRate[0];

        $intrestRatePerAnnum = $intrestRate[1];

        $maturityAmount = $amount + $intrestAmount;

        if($accountType === 'single'){
            $update_balance_stmt=$conn->prepare("UPDATE ind_accounts SET balance = balance - ? WHERE user_id = ?");
            $update_balance_stmt->bind_param("di",$amount,$userId);
        }
        elseif($accountType === "joint"){
           
            $update_balance_stmt = $conn->prepare("UPDATE joint_accounts SET balance = balance - ? WHERE user1_id = ? OR user2_id = ?");
            $update_balance_stmt ->bind_param("dii",$amount,$userId,$userId);
            
        }
        else{
            $conn->close();
            return false;
        }

        if($update_balance_stmt->execute()){
            
            $transactionType = 'fixed Deposit';
            $description = "Fixed Deposite -$amount";
           

            $trans_stmt = $conn->prepare("INSERT INTO transactions(user_id, account_type, transaction_type, amount, description) VALUES (?, ?, ?, ?, ?)");
            $trans_stmt->bind_param("issds", $userId, $accountType, $transactionType, $amount, $description);
            $trans_stmt->execute();
            $trans_stmt->close();
           
        }
        else{
            $conn->close();
            return false;
        }

        $fd_stmt = $conn->prepare("INSERT INTO fixed_deposits (user_id, principal_amount, interest_rate,intrest_amount,maturity_amount,duration_in_months) VALUES (?,?,?,?,?,?)");
        $fd_stmt->bind_param("ididdi", $userId, $amount, $intrestRatePerAnnum,$intrestAmount, $maturityAmount, $durationInMonths);
        $fd_stmt->execute();
        $fd_stmt->close();
        
        $conn->close();

        return true;

    }

    public function createRecurringDeposit($userId,$monthlyAmount,$durationInMonths){

        $conn = DBConnection::getConnection();
       
       
        if ($monthlyAmount <= 0 || $durationInMonths <= 0) {
            // echo "failed1";
            $conn->close();
            return false;
        }
        $userBalance = $this->getUserAccountBalance($userId);

        $accountType = $this->getAccountType($userId);

        if( $userBalance < $monthlyAmount ){
            // echo "failed2";
            $conn->close();
            return false;
        }
       
        $intrestRate = $this->calculateRdIntrestRate($monthlyAmount,$durationInMonths);
        
        $intrestAmount = $intrestRate[0];
        // echo $intrest_rate[1];
        $intrestRatePerAnnum = $intrestRate[1];
        
        $principleAmount = ($monthlyAmount * $durationInMonths);

        $maturityAmount = $principleAmount + $intrestAmount;

        if($accountType === 'single'){
            // echo  $durationInMonths;
            $update_balance_stmt=$conn->prepare("UPDATE ind_accounts SET balance = balance - ? WHERE user_id = ?");
            $update_balance_stmt->bind_param("di",$monthlyAmount,$userId);
        }
        elseif($accountType === "joint"){
            // echo  $durationInMonths;
            $update_balance_stmt = $conn->prepare("UPDATE joint_accounts SET balance = balance - ? WHERE user1_id = ? OR user2_id = ?");
            $update_balance_stmt ->bind_param("dii",$monthlyAmount,$userId,$userId);
            
        }
        else{
            echo  $durationInMonths;
            $conn->close();
            return false;
        }

        if($update_balance_stmt->execute()){
            
            $transactionType = 'Recurring Deposit';
            $description = "Recurring Deposit -$monthlyAmount";
           

            $trans_stmt = $conn->prepare("INSERT INTO transactions(user_id, account_type, transaction_type, amount, description) VALUES (?, ?, ?, ?, ?)");
            $trans_stmt->bind_param("issds", $userId, $accountType, $transactionType, $monthlyAmount, $description);
            $trans_stmt->execute();
            $trans_stmt->close();
           
        }
        else{
            $conn->close();
            return false;
        }
        // echo $intrest_rate_per_annum;
        $rd_stmt = $conn->prepare("INSERT INTO recurring_deposits (user_id, monthly_amount, interest_rate,intrest_amount,maturity_amount,duration_in_months,principal_amount) VALUES (?,?,?,?,?,?,?)");
        $rd_stmt->bind_param("ididdid", $userId, $monthlyAmount, $intrestRatePerAnnum,$intrestAmount, $maturityAmount, $durationInMonths,$principleAmount);
        $rd_stmt->execute();
        $rd_stmt->close();
        
        $conn->close();

        return true;

     }

    public function getAccTypeUserIdFromAccountNumber($accountNumber){

        $conn = DBConnection::getConnection();

        $query = "SELECT u.account_type,u.id
              FROM users u
              LEFT JOIN ind_accounts ia ON u.id = ia.user_id
              LEFT JOIN joint_accounts ja ON u.id = ja.user1_id OR u.id = ja.user2_id
              WHERE ia.account_number = ? OR ja.account_number = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $accountNumber, $accountNumber);
        $stmt->execute();

       
        $stmt->bind_result($account_type,$userId);
        if ($stmt->fetch()) {    
            $stmt->close();
            $conn->close();
            return ['account_type'=>$account_type,'userId' =>$userId];
        }
        $stmt->close();
        $conn->close();
        return null;

    }

    public function transferFunds($userId,$reciAccNum,$transAmount){

        $conn = DBConnection::getConnection();

        

        if ($transAmount <= 0) {
            
            $conn->close();
            return false;
        }
         

        $senderBalance = $this->getUserAccountBalance($userId);
        
        $senderAccNum =$this->getUserAccountNumber($userId);

       

        $senderAccountType = $this->getAccountType($userId);
        
        $receiverAccountTypeId = $this->getAccTypeUserIdFromAccountNumber($reciAccNum);

        $receiverAccountType = $receiverAccountTypeId['account_type'];

        $receiverId = $receiverAccountTypeId['userId'];
       
        if ($transAmount > $senderBalance) {
            // echo $reci_acc_num;
            $conn->close();
            return false;
        }
        
        if ($senderAccountType === 'single') {
            
            $sender_update_stmt = $conn->prepare("UPDATE ind_accounts SET balance = balance - ? WHERE user_id = ?");
            $sender_update_stmt->bind_param("di", $transAmount, $userId);
        } 
        else if ($senderAccountType === 'joint') {

            $sender_update_stmt = $conn->prepare("UPDATE joint_accounts SET balance = balance - ? WHERE user1_id = ? OR user2_id=?");
            $sender_update_stmt->bind_param("dii", $transAmount, $userId,$userId);
            // echo $sender_acc_num;
        } 
        else {
            
            $conn->close();
            return false;
        }

        
        $sender_update_stmt->execute();
        $sender_update_stmt->close();

        if ($receiverAccountType === 'single') {
            
            $receiver_update_stmt = $conn->prepare("UPDATE ind_accounts SET balance = balance + ? WHERE account_number = ?");
            
        } 
        else if ($receiverAccountType === 'joint') {
            $receiver_update_stmt = $conn->prepare("UPDATE joint_accounts SET balance = balance + ? WHERE account_number = ?");
        }
         else {
            
            $conn->close();
            return false;
        }

        $receiver_update_stmt->bind_param("ds",$transAmount,$reciAccNum);
        
        $receiver_update_stmt->execute();
        $receiver_update_stmt->close();


        $transactionType = 'fund_transfer';
        $senderDescription = "Transferred $transAmount to Account Number: $reciAccNum";
        

        $sender_transaction_stmt = $conn->prepare("INSERT INTO transactions (user_id, account_type, transaction_type, amount, description) VALUES (?, ?, ?, ?, ?)");
        $sender_transaction_stmt->bind_param("issds", $userId, $senderAccountType, $transactionType, $transAmount, $senderDescription);
        
        $sender_transaction_stmt->execute();
        // echo $reci_acc_num;
        $sender_transaction_stmt->close();

        $receiverDescription = "Received $transAmount from Account Number: $senderAccNum";

        $receiver_transaction_stmt = $conn->prepare("INSERT INTO transactions (user_id, account_type, transaction_type, amount, description) VALUES (?, ?, ?, ?, ?)");
        $receiver_transaction_stmt->bind_param("issds", $receiverId, $receiverAccountType, $transactionType, $transAmount, $receiverDescription);
        $receiver_transaction_stmt->execute();
        $receiver_transaction_stmt->close();

        
        $conn->close();

        
        return true;

    }
    

    
}


?>