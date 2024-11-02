<?php

session_start();
if (isset($_GET['page'])) {
    $actions = array('config.php', 'functions.php');
    $page = $_GET['page'];
    if (! in_array($page, $actions)){
     $page = 'error';}
    
    include($page);}
include 'functions.php';

// Generate the CSRF token and saved in the session
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$token = $_SESSION['csrf_token'];

// Check if user is logged in with valid cookie using function type
validateCookie($db);

$loginid = $_COOKIE['loggedin'];
$stmt = $db->prepare("SELECT * FROM users WHERE loginid = :loginid");

// bind the variable to the named sql placeholder
$stmt->bindParam(':loginid', $loginid, PDO::PARAM_STR);
//告訴程式，前面的變數要使用字串的型態來填入，為的也是防止 Injection
$stmt->execute();

// fetch the result as an associative array
//If the query executed by $stmt->execute() returns no rows, then $stmt->fetch(PDO::FETCH_ASSOC) will return false.
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {  //checks if the current request method is POST, check the form has been submitted using the POST method

    if (!isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'],$_POST['csrf_token'])) {// mitigate timing attacks 
        //measure the time difference between two comparisons and deduce that the second string is more correct (since the second comparison took more time). 
        //hash_equals eliminates this type of attack by always comparing all characters of both strings, not matter if they match or not. So more and less correct strings will take the same time.
        die("CSRF token verification failed");
    }
    


    //將 HTML 符號變成不可執行的符號，例如有人利用網站表單輸入一些清除資料庫的語法或塞入後門程式，通常都會用到一些特殊符號，為了安全起見，
    //所有表單傳遞的資料都應該利用 PHP htmlspecialchars 函數做第一層的把關
    $title = filter_var(htmlspecialchars($_POST["title"]), FILTER_SANITIZE_STRING);
    $description = filter_var(htmlspecialchars($_POST["description"]), FILTER_SANITIZE_STRING);
    $preferred_size = filter_var($_POST["preferred_size"], FILTER_SANITIZE_NUMBER_INT);
    $status = ($preferred_size == 1) ? 'complete' : 'ongoing'; // set status based on preferred size
    
    // Insert group request into database
 $stmt = $db->prepare("INSERT INTO group_requests (title, description, preferred_size, loginid, status,creator) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bindParam(1, $title);
$stmt->bindParam(2, $description);
$stmt->bindParam(3, $preferred_size);
$stmt->bindParam(4, $loginid);
$stmt->bindParam(5, $status);
$stmt->bindParam(6, $user['nickname']);
$stmt->execute();
    
    // Get the ID of the newly created group
    //需要獲取剛剛插入到數據庫表中的行的自動遞增 ID 值。可以使用lastInsertId();方法實現此目的。
    $group_id = $db->lastInsertId();

  // Insert the user into the group_members table with creator name
$stmt = $db->prepare("INSERT INTO group_members (group_id, loginid, join_date) VALUES (?, ?, NOW())");
$stmt->bindParam(1, $group_id);
$stmt->bindParam(2, $loginid);
$stmt->execute();
    
    // Redirect to ongoing_group_requests.php
    header("Location: ongoing_group_requests.php");
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Create Group Request</title>
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Create Group Request</div>

                <div class="card-body">
                    <form method="post">
                        <!--For avoid CSRF-->
                    <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                        <div class="form-group">
                            <label for="title">Title:</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea class="form-control" id="description" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="preferred_size">Preferred Size:</label>
                            <input type="number" min="1" class="form-control" id="preferred_size" name="preferred_size" pattern="\d+" required>
                            <!--pattern="\d+" -> Regular expression , for preventing users from entering negative values-->

                        </div>
                        <button type="submit" class="btn btn-primary">Create Group Request</button>
                    </form>
                </div>

                <div class="card-footer">
                    <a href="home.php">Home</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
