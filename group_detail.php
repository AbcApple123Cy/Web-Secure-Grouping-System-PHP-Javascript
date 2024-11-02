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

// Generate CSRF token and store it in session
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        die('CSRF token validation failed');
    }
    
    // Sanitize user input
    $group_id = filter_input($group_request['group_id'], FILTER_SANITIZE_NUMBER_INT);
    
    // Retrieve group information from database with Prepared Statements
    $group_stmt = $db->prepare("SELECT * FROM group_requests WHERE group_id = ?");
    $group_stmt->execute([$group_id]);
    $group = $group_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if user is already a member of the group
    $member_stmt = $db->prepare("SELECT * FROM group_members WHERE loginid = ? AND group_id = ?");
    $member_stmt->execute([$loginid, $group_id]);
    $member = $member_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$group) {
        die('Invalid group ID');
    }
    
    // Process form action based on whether user is a member of the group or not
    if ($member) {
        // User is already a member of the group, so leave the group
        $leave_stmt = $db->prepare("DELETE FROM group_members WHERE loginid = ? AND group_id = ?");
        $leave_stmt->execute([$loginid, $group_id]);
        header('Location: group_requests.php');
        exit;
    } else {
        // User is not a member of the group, so join the group
        $join_stmt = $db->prepare("INSERT INTO group_members (loginid, group_id) VALUES (?, ?)");
        $join_stmt->execute([$loginid, $group_id]);
        header('Location: group_requests.php');
        exit;
    }
}

// Retrieve all group requests from database with Prepared Statements
$request_stmt = $db->prepare("SELECT group_requests.*, COUNT(group_members.loginid) AS current_size 
                      FROM group_requests 
                      LEFT JOIN group_members ON group_requests.group_id = group_members.group_id
                      GROUP BY group_requests.group_id");
$request_stmt->execute();
$group_requests = $request_stmt->fetchAll(PDO::FETCH_ASSOC);

// Retrieve all groups that the user is a member of with Prepared Statements
$member_stmt = $db->prepare("SELECT * FROM group_members WHERE loginid = ?");
$member_stmt->execute([$loginid]);
$user_groups = $member_stmt->fetchAll(PDO::FETCH_ASSOC);
$user_group_ids = array_column($user_groups, 'group_id');

?>

<!DOCTYPE html>
<html>
<head>
    <title>Group Requests</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                
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
                                    <td><?php echo htmlspecialchars($group_request['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($group_request['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($group_request['preferred_size'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($group_request['current_size'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <?php if ($group_request['status'] == 'ongoing') {
                                            $group_id = $group_request['group_id'];
                                            if (in_array($group_id, $user_group_ids)) {
                                                // User is already a member of the group
                                                echo '<a href="leave_group.php?group_id=' . intval($group_id) . '" class="btn btn-danger">Leave Group</a>';
                                            } else {
                                                // User is not a member of the group
                                                echo '<a href="join_group.php?group_id=' . intval($group_id) . '" class="btn btn-success">Join Group</a>';
                                            }
                                        } ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($group_request['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><a href="view_group_detail.php?group_id=<?php echo intval($group_request['group_id']); ?>" class="btn btn-primary">View</a></td>
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
