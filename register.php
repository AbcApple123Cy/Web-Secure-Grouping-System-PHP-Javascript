<?php
 if (isset($_GET['page'])) {
    $actions = array('config.php', 'functions.php');
    $page = $_GET['page'];
    if (! in_array($page, $actions)){
     $page = 'error';}
    
    include($page);}
    
require_once('config.php');

// Check if user is already logged in
if(isset($_COOKIE['loggedin'])) {
    $loginid = $_COOKIE['loggedin'];}
    
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

if(isset($_POST['register'])) {
    // get input values from form
    // both functions together to sanitize user input. htmlspecialchars() used to escape HTML characters to prevent XSS attacks
    //while FILTER_SANITIZE_STRING s used to remove  remaining special characters and non-printable characters.
    $loginid = filter_var(htmlspecialchars($_POST['loginid']), FILTER_SANITIZE_STRING);
    $nickname = filter_var(htmlspecialchars($_POST['nickname']), FILTER_SANITIZE_STRING);
    $email =  filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // sanitize email, remove all illegal characters from email
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // hash the password

 // check if user with same login ID or email already exists in database with prepared statement.
 $stmt = $db->prepare("SELECT * FROM users WHERE loginid = ? OR email = ?");
 $stmt->execute(array($loginid, $email));
 $user = $stmt->fetch(PDO::FETCH_ASSOC);
 
 if($user) {
     // user already exists
     $msg = "User with same login ID or email already exists.";
 } else {
     // insert new user into database
     $stmt = $db->prepare("INSERT INTO users (loginid, nickname, email, password) VALUES (?, ?, ?, ?)");
     $stmt->execute(array($loginid, $nickname, $email, $password));
     $msg = "User registered successfully. Please <a href='index.php'>login</a> to continue.";
 }
 

}

?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">User Registration</div>

                <div class="card-body">
                    <?php if(isset($msg)) { ?>
                    <div class="alert alert-<?php echo $user ? 'danger' : 'success'; ?>" role="alert">
                        <?php echo $msg; ?>
                    </div>
                    <?php } ?>

                    <form method="POST" action="register.php">
                        <div class="form-group">
                            <label for="loginid">Login ID</label>
                            <input type="text" class="form-control" name="loginid" required>
                        </div>

                        <div class="form-group">
                            <label for="nickname">Nick Name</label>
                            <input type="text" class="form-control" name="nickname" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>

                        <button type="submit" name="register" class="btn btn-primary">Register</button>
                    </form>

                    <div class="mt-3">
                        Already have an account? <a href="index.php">Login here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
