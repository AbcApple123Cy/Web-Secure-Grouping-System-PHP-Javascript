<?php
require_once 'functions.php';

// Check if user is logged in with valid cookie using function type
validateCookie($db);

// Check if group_id is set in the URL
if (!isset($_GET['group_id'])) {
    header("Location: ongoing_group_requests.php");
    exit();
}

//make sure the received group id is safe
$group_id = filter_var($_GET['group_id'], FILTER_SANITIZE_NUMBER_INT);
$loginid = $_COOKIE['loggedin'];


// Check if the user is already a member of the group with prepared statement

$stmt = $db->prepare("SELECT * FROM group_members WHERE group_id = :groupid AND loginid = :loginid");
$stmt->bindValue(':groupid', $group_id );
$stmt->bindValue(':loginid', $loginid);
$stmt->execute();
$group_member = $stmt->fetch(PDO::FETCH_ASSOC);

if ($group_member) {
    // User is already a member of the group
    header("Location: view_group_detail.php?group_id=$group_id");
    exit();
}

// Check if the group request is still ongoing
$stmt = $db->prepare("SELECT * FROM group_requests WHERE group_id = ? AND status = 'ongoing'");
$stmt->execute([$group_id]);
$group_request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$group_request) {
    // Group request is not ongoing
    header("Location: ongoing_group_requests.php");
    exit();
}

// Check if the group has already reached its preferred size
$stmt = $db->prepare("SELECT COUNT(*) AS current_size FROM group_members WHERE group_id = ?");
$stmt->execute([$group_id]);
$group_size = $stmt->fetch(PDO::FETCH_ASSOC);

if ($group_size['current_size'] >= $group_request['preferred_size']) {
    // Group has already reached its preferred size
    header("Location: view_group_detail.php?group_id=$group_id");
    exit();
}

// Add the user to the group using Prepared the statement
// 
$stmt = $db->prepare("INSERT INTO group_members (group_id, loginid) VALUES (?, ?)");


$stmt->bindParam(1, $group_id, PDO::PARAM_INT);
$stmt->bindParam(2, $loginid, PDO::PARAM_STR);

$stmt->execute();






// Update current_size of group_request by 1
$stmt = $db->prepare("UPDATE group_requests SET current_size = current_size + 1 WHERE group_id = ?");
$stmt->execute([$group_id]);

// Update the group request status to "complete" if the preferred size is reached
if ($group_size['current_size'] + 1 == $group_request['preferred_size']) {
    $stmt = $db->prepare("UPDATE group_requests SET status = 'complete' WHERE group_id = ?");
    $stmt->execute([$group_id]);
}


header("Location: view_group_detail.php?group_id=$group_id");
exit();
?>
