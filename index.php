<?php







/*the samesite parameter to "Strict" when calling session_set_cookie_params(). The session_start() function is then called to start the session with the specified session cookie parameters.
 */
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

header("Content-Security-Policy: default-src *");
header("X-Content-Type-Options: nosniff");



//To stop/disable directory browsing,  can add the following code to  website's .htaccess file: "Options -Indexes" "Header always unset X-Frame-Options" for  Missing Anti-clickjacking Header


// for CSRF protection only 


function generateCSRFToken() {
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    return $token;
}





/*//The in_array() function searches an array for a specific value.
//in_array(search, array, type)
$_GET['page']; is a superglobal variable in PHP that retrieves the value of a query string parameter named 'page' from the URL
the value passed through the query string parameter page will be retrieved from the URL. 
current value of $page is not one of the values in the $actions array, set $page to 'error'.
 the value passed through the query string parameter page will be retrieved from the URL. */ 


 if (isset($_GET['page'])) {
$actions = array('config.php', 'functions.php');
$page = $_GET['page'];
if (! in_array($page, $actions)){
 $page = 'error';}

include($page);}

require_once('config.php');

// Check if user is already logged in
if(isset($_COOKIE['loggedin'])) {
    $loginid = $_COOKIE['loggedin'];
    
    // Retrieve user information from database
    $stmt = $db->prepare("SELECT * FROM users WHERE loginid = :loginid");
$stmt->bindParam(':loginid', $loginid, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);


    if($user) {
        // Valid cookie, redirect to home page
        header("Location: home.php");
        exit;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') { //redefined variable in PHP that contains the request method used to access the current script. 
    // checks that the HTTP method used for the request is actually POST. makes it more difficult for an attacker to craft a malicious request as they would need to fake a complete POST request,
    // including headers and data, rather than just setting a specific POST parameter.


     // "::"used when when we want to access constants, properties and methods defined at class level.
 

    $stmt = $db->prepare("SELECT * FROM users WHERE loginid = :loginid");
$stmt->bindParam(':loginid', $loginid, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

 

// Compares two strings using the same time whether they're equal or not. because sometime there is some time difference if the token is close to the correct one
if (!isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'],$_POST['csrf_token'])) {// mitigate timing attacks 
    //measure the time difference between two comparisons and deduce that the second string is more correct (since the second comparison took more time). 
    //hash_equals eliminates this type of attack by always comparing all characters of both strings, not matter if they match or not. So more and less correct strings will take the same time.
    die("CSRF token verification failed");
}


// get input values from form
$loginid = filter_var(htmlspecialchars($_POST['loginid']), FILTER_SANITIZE_STRING);
$password = filter_var(htmlspecialchars($_POST['password']), FILTER_SANITIZE_STRING);
$Password = password_hash($password, PASSWORD_BCRYPT);

    //the server hashes the password they entered using the same algorithm and compares it to the hashed password stored in the database.

  // Retrieve user information from database with Prepared Statements
  $stmt = $db->prepare("SELECT * FROM users WHERE loginid = :loginid");

  //告訴程式，前面的變數要使用字串的型態來填入，為的也是防止 Injection
  $stmt->bindParam(':loginid', $loginid, PDO::PARAM_STR);
  $stmt->execute();
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  



    if($user) {

//lock if the locked time is not yet over
        if($user['locked_until'] > time()) {
            $remaining_time = $user['locked_until'] - time();
            die("Your account has been locked due to too many failed login attempts. Please try again later. Remaining time: " . $remaining_time . " seconds.");
        }
        
        // verify password
        /*password_verify() is is used to verify if a given plain-text password matches the hashed password. 
        It automatically takes care of hashing the plain-text password using the same algorithm and salt that were used to hash the original password, and then compares the two hashed values to check if they match. */
        

        if(!password_verify($password, $user['password'])){

  //for limit attempts

     $login_attempts= $user['attempts'];
      $login_attempts++;
      $stmt = $db->prepare("UPDATE users SET attempts = :attempts WHERE loginid = :loginid");
      $stmt->bindParam(':attempts',  $login_attempts, PDO::PARAM_INT);   
      $stmt->bindParam(':loginid', $loginid, PDO::PARAM_STR);
      $stmt->execute();
  
  
  if($login_attempts >= 3) {
      // lock user account for 10 minutes
     /* time() 取得系統的 Unix 時間戳 (timestamp)
time();
舉例來說取得當下的時間如果是 2021年1月1日的00:00:00，那麼這個時間的 Unix時間 將會是 1609430400*/

      $locked_until = time() + 10; // 10 minutes
      $stmt = $db->prepare("UPDATE users SET locked_until = :locked_until WHERE loginid = :loginid");
      $stmt->bindParam(':locked_until', $locked_until, PDO::PARAM_INT);   
      $stmt->bindParam(':loginid', $loginid, PDO::PARAM_STR);
      $stmt->execute();
      
      die("Your account has been locked due to too many failed login attempts. Please try again later.");
  }

}







        if(password_verify($password, $user['password'])) {


            $stmt = $db->prepare("UPDATE users SET attempts = :attempts WHERE loginid = :loginid");
            $attempts = 0;
$stmt->bindParam(':attempts', $attempts, PDO::PARAM_INT);   //bindParam()  expects the second parameter to be a variable reference  bind a literal value to a prepared statement parameter --> bindValue() 
            $stmt->bindParam(':loginid', $loginid, PDO::PARAM_STR);
            $stmt->execute();


            // password is correct, set secure cookie and redirect to home.php
            /*攻擊者會發現即使網站有 XSS 漏洞，也成功的植入了惡意程式，但卻沒有將你受到 HttpOnly 保護的 Cookie 值傳送回來，為什麼會這樣呢？

原因是因為 HttpOnly 的功能就是拒絕與 JavaScript 共享 Cookie ，當 Cookie 中包含了設定 HttpOnly 的值之後，HttpOnly 會拒絕這個請求，藉此來保護 Cookie 不被不受信任的 JavaScript 存取, 當之無愧的 XSS 之敵。 */



            //Cookie 本身無法被設定為只在 First-party 環境才發送，因此 Request 在任何環境都會帶上 Cookie，Server 無法辨識 Request 來源只能照常回覆，同時也讓 Client 浪費流量送出無用的 Cookie，
//有了 SameSite 屬性後，就可以個別設定 Cookie 在不同環境下的發送條件。
setcookie('loggedin', $user['loginid'], [
    'expires' => time() + 3600,
    'path' => '/',
    'domain' => 'cyrusgroupingsystem.buzz',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
  ]);
    
    
    // cookie 應僅通過來自客戶端的安全 HTTPS 連接傳輸。設置為 時true，只有存在安全連接時才會設置 cookie。在服務器端，程序員只能在安全連接上發送這種 cookie


                /*SameSite 這個屬性的特性，瀏覽器會檢查 Request 的來源是否與發布 Cookie 的來源相同。
如果不是，瀏覽器就不會在 Request 中包含Cookie，因此便可以從根本上 CSRF 的攻擊來阻止。
Strict：這是限制最嚴格的設定，會完全禁止第三方的 Cookie 請求，基本上只有在網域和 URL 中的網域相同，才會傳遞 Cookie 請求，舉例來說，當網站裡有一個 FB 連結時，點開連結，使用者必須要再登入一次 FB ，因為網站沒有傳送任何 FB 的 Cookie 。 */
              
unset($_SESSION['csrf_token']);
// regenerate the CSRF token after each form submission to prevent reusing the same token:
            header('Location: home.php');
            
            exit;
        } else {
            // password is incorrect
            $msg = "Incorrect password.";
        }
    } else {
        // user not found
        $msg = "User with login ID '$loginid' not found.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">User Login</div>

                <div class="card-body">
                    <?php if(isset($msg)) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $msg; ?>
                    </div>
                    <?php } ?>

                    <form method="POST" action="index.php">    

                    <!-- adding a hidden field to your form that contains a value that only you and your user know.
                    This ensures that the user - not some other entity - is submitting the given data. -->
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="form-group">
                            <label for="loginid">Login ID</label>
                            <input type="text" class="form-control" name="loginid" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>

                        <button type="submit" name="login" class="btn btn-primary">Login</button>
                    </form>

                    <div class="mt-3">
                        Don't have an account? <a href="register.php">Register here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>