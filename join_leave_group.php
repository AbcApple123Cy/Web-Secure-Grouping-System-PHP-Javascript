
<?php


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

// Retrieve all group requests from database
$stmt = $db->prepare("SELECT group_requests.*, COUNT(group_members.loginid) AS current_size 
                      FROM group_requests 
                      LEFT JOIN group_members ON group_requests.group_id = group_members.group_id
                      WHERE group_requests.status = 'ongoing' AND group_requests.preferred_size > 0 
                      GROUP BY group_requests.group_id");
$stmt->execute();
$group_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Retrieve all groups that the user is a member of group 
$stmt = $db->prepare("SELECT * FROM group_members WHERE loginid = ?");
$stmt->bindParam(1, $loginid, PDO::PARAM_STR);
$stmt->execute();

$user_groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
$user_group_ids = array_column($user_groups, 'group_id');

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
                                <th>Title</th>
                                <th>Description</th>
                                <th>Preferred Size</th>
                                <th>Current Size</th>
                                <th>Action</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($group_requests as $group_request) { ?>
                                <tr>
                                    <td><?php echo $group_request['title']; ?></td>
                                    <td><?php echo $group_request['description']; ?></td>
                                    <td><?php echo $group_request['preferred_size']; ?></td>
                                    <td><?php echo $group_request['current_size']; ?></td>
                                    <td>
                                        <?php if ($group_request['status'] == 'ongoing') {
                                            $group_id = $group_id = filter_var($group_request['group_id'], FILTER_SANITIZE_NUMBER_INT);
                                            if (in_array($group_id, $user_group_ids)) {
                                                // User is already a member of the group
                                                echo '<a href="leave_group_ongoing.php?group_id=' . $group_id . '" class="btn btn-danger">Leave Group</a>';
                                            } else {
                                                // User is not a member of the group
                                                echo '<a href="join_group_ongoing.php?group_id=' . $group_id . '" class="btn btn-success">Join Group</a>';
                                            }
                                        } ?>
                                    </td>
                                    <td><?php echo $group_request['status']; ?></td>
                                    <td><a href="view_group_detail.php?group_id=<?php echo $group_request['group_id']; ?>" class="btn btn-primary">View</a></td>
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
