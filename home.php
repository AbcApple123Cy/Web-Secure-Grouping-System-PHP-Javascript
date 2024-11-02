<?php

require_once('functions.php');

if (isset($_GET['page'])) {
    $actions = array('config.php', 'functions.php');
    $page = $_GET['page'];
    if (! in_array($page, $actions)){
     $page = 'error';}
    
    include($page);}

    require_once('functions.php');
// Check if user is logged in with valid cookie using function type
validateCookie($db);

$loginid = $_COOKIE['loggedin'];

// Retrieve user information from database with Prepared Statements with verify security of cookie
$user = retrieve($db, $loginid);

?>

<!DOCTYPE html>
<html>
<head>
	<title>Home</title>
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Welcome <?php echo $user['nickname']; ?></div>

                <div class="card-body">
                    <h5 class="card-title">Choose an action:</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><a href="create_group_request.php">User Create Group Request</a></li>
                        <li class="list-group-item"><a href="ongoing_group_requests.php">List of Ongoing Group Requests</a></li>
                        <li class="list-group-item"><a href="my_groups.php">List of My Groups</a></li>
                        <li class="list-group-item"><a href="join_leave_group.php">User Join/Leave Group</a></li>
                        <li class="list-group-item"><a href="group_detail.php">Group Detail</a></li>
                    </ul>
                </div>

                <div class="card-footer">
                    <a href="logout.php">Log Out</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
