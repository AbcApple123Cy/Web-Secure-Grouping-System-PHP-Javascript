<?php



// Check if user is logged in with valid cookie using function type
//The $db object is a database connection object created using PDO
//It is used to communicate with the database and execute SQL queries.
//PHP 數據對象 （PDO） 擴展為PHP訪問數據庫定義了一個輕量級的一致接口。
//PDO 提供了一個數據訪問抽象層，這意味著，不管使用哪種數據庫，
//都可以用相同的函數（方法）來查詢和獲取數據。
//Without the $db object, the function would 
//not be able to access the database and retrieve the necessary information.
// we have created $db in config.php

require_once 'functions.php';

if (isset($_GET['page'])) {
    $actions = array('config.php', 'functions.php');
    $page = $_GET['page'];
    if (! in_array($page, $actions)){
     $page = 'error';}
    
    include($page);}
// Check if user is logged in with valid cookie using function type
validateCookie($db);

$loginid = $_COOKIE['loggedin'];

// Retrieve user information from database with Prepared Statements
$user = retrieve($db, $loginid);

// Retrieve ongoing group requests from database
$stmt = $db->prepare("SELECT group_requests.*, COUNT(group_members.loginid) AS current_size 
                      FROM group_requests 
                      LEFT JOIN group_members ON group_requests.group_id = group_members.group_id 
                      WHERE group_requests.status = 'ongoing' AND group_requests.preferred_size > 0 
                      GROUP BY group_requests.group_id");
$stmt->execute();
$group_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ongoing Group Requests</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Ongoing Group Requests</div>

                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Group Name</th>
                                <th>Description</th>
                                <th>Preferred Size</th>
                                <th>Current Size</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
    <?php foreach ($group_requests as $group_request) { ?>
        <tr>
        
      
      
       

            <td><?php filter_var(htmlspecialchars($group_request['title']), FILTER_SANITIZE_STRING); echo $group_request['title']; ?></td>
            <td><?php echo   filter_var(htmlspecialchars($group_request['description']), FILTER_SANITIZE_STRING); $group_request['description']; ?></td>
            <td><?php echo   filter_var(htmlspecialchars($group_request['preferred_size']), FILTER_SANITIZE_NUMBER_INT); $group_request['preferred_size']; ?></td>
            <td><?php echo  filter_var(htmlspecialchars($group_request['current_size']), FILTER_SANITIZE_NUMBER_INT); $group_request['current_size']; ?></td>

            <!-- INPUT_GET  means the input source for GET variables  used together with the filter_input() function to sanitize and validate GET variables.-->
            <td><a href= view_group_detail.php?group_id=<?php $group_id = filter_input( $group_request['group_id'], FILTER_SANITIZE_NUMBER_INT);
            
            
            echo $group_request['group_id']; ?> class="btn btn-primary">View</a></td>
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
