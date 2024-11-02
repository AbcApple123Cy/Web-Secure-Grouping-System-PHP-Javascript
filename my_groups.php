<?php



if (isset($_GET['page'])) {
    $actions = array('config.php', 'functions.php');
    $page = $_GET['page'];
    if (! in_array($page, $actions)){
     $page = 'error';}
    
    include($page);}
    
    require_once 'functions.php';

// Check if user is logged in with valid cookie using function type
validateCookie($db);

$loginid = $_COOKIE['loggedin'];

// Retrieve user information from database with Prepared Statements
$user = retrieve($db, $loginid);

// Retrieve user's joined groups from database
$stmt = $db->prepare("SELECT group_requests.*, COUNT(group_members.loginid) AS current_size 
                      FROM group_requests 
                      INNER JOIN group_members ON group_requests.group_id = group_members.group_id 
                      WHERE group_members.loginid = :loginid AND group_requests.status = 'ongoing'
                      GROUP BY group_requests.group_id");

$stmt->bindParam(':loginid', $loginid, PDO::PARAM_STR);
$stmt->execute();

$my_groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>My Groups</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">My Groups</div>

                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Preferred Size</th>
                                <th>Current Size</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_groups as $my_group) { ?>
                                <tr>
                                    <td><?php echo $my_group['title']; ?></td>
                                    <td><?php echo $my_group['description']; ?></td>
                                    <td><?php echo $my_group['preferred_size']; ?></td>
                                    <td><?php echo $my_group['current_size']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
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
