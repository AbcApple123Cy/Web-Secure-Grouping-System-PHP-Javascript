<?php
require_once 'functions.php';

validateCookie($db);

// Retrieve user information from database with Prepared Statements
$loginid = $_COOKIE['loggedin'];
$user = retrieve($db, $loginid);

// Retrieve group information from database
$group_id = $_GET['group_id'];
$stmt = $db->prepare("SELECT * FROM group_requests WHERE group_id = ?");
$stmt->bindParam(1, $group_id, PDO::PARAM_INT);
$stmt->execute();
$group_request = $stmt->fetch(PDO::FETCH_ASSOC);


// Retrieve group members information from database
$stmt = $db->prepare("SELECT group_members.*, users.nickname
                      FROM group_members 
                      INNER JOIN users ON group_members.loginid = users.loginid 
                      WHERE group_members.group_id = :group_id");
$stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
$stmt->execute();
$group_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Group Detail</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><p>Title <?php filter_var(htmlspecialchars($group_request['title']), FILTER_SANITIZE_STRING); echo $group_request['title']; ?></div> </p>

                <div class="card-body">
                    
                    <p>Description: <?php filter_var(htmlspecialchars($group_request['description']), FILTER_SANITIZE_STRING); echo $group_request['description']; ?></p>

                    <p>Preferred Size: <?php filter_var(htmlspecialchars($group_request['preferred_size']), FILTER_SANITIZE_STRING); echo $group_request['preferred_size']; ?></p>

                    <p>Current Size: <?php filter_var(($group_members), FILTER_SANITIZE_NUMBER_INT); echo count($group_members); ?></p>

                    <p>Creator: <?php filter_var(htmlspecialchars($group_request['creator']), FILTER_SANITIZE_STRING); echo $group_request['creator']; ?></p>

                    <p>Group Members:</p>
                    <ul>
                        <?php foreach ($group_members as $group_member) { ?>
                            <li><?php filter_var(htmlspecialchars($group_member['nickname']), FILTER_SANITIZE_STRING); echo $group_member['nickname']; ?></li>
                        <?php } ?>
                    </ul>
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
