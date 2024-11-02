<?php

session_start();
require_once 'functions.php';

// Check if user is logged in with valid cookie using function type
validateCookie($db);

// Check if group_id is set in the URL
if (!isset($_GET['group_id'])) {
    header("Location: ongoing_group_requests.php");
    exit();
}

$group_id = filter_var($_GET['group_id'], FILTER_SANITIZE_NUMBER_INT);
$loginid = $_COOKIE['loggedin'];

// Check if the user is already a member of the group
$stmt = $db->prepare("SELECT * FROM group_members WHERE group_id = :group_id AND loginid = :loginid");
$stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
$stmt->bindParam(':loginid', $loginid, PDO::PARAM_STR);
$stmt->execute();
$group_member = $stmt->fetch(PDO::FETCH_ASSOC);


if ($group_member) {
    // User is already a member of the group
    header("Location: view_group_detail.php?group_id=$group_id");
    exit();
}

// Check if the group request is still ongoing
$stmt = $db->prepare("SELECT * FROM group_requests WHERE group_id = :group_id AND status = 'ongoing'");
$stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
$stmt->execute();
$group_request = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$group_request) {
    // Group request is not ongoing
    header("Location: ongoing_group_requests.php");
    exit();
}

// Check if the group has already reached its preferred size
$stmt = $db->prepare("SELECT COUNT(*) AS current_size FROM group_members WHERE group_id = :group_id");
$stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
$stmt->execute();
$group_size = $stmt->fetch(PDO::FETCH_ASSOC);

if ($group_size['current_size'] >= $group_request['preferred_size']) {
    // Group has already reached its preferred size
    header("Location: view_group_detail.php?group_id=$group_id");
    exit();
}

// Add the user to the group
$stmt = $db->prepare("INSERT INTO group_members (group_id, loginid) VALUES (?, ?)");
$stmt->bindParam(1, $group_id, PDO::PARAM_INT);
$stmt->bindParam(2, $loginid, PDO::PARAM_STR);
$stmt->execute();

// Update current_size of group_request by 1
$stmt = $db->prepare("UPDATE group_requests SET current_size = current_size + 1 WHERE group_id = ?");
$stmt->bindParam(1, $group_id, PDO::PARAM_INT);
$stmt->execute();

// Update the group request status to "complete" if the preferred size is reached
if ($group_size['current_size'] + 1 == $group_request['preferred_size']) {
    $stmt = $db->prepare("UPDATE group_requests SET status = 'complete' WHERE group_id = ?");
    $stmt->bindParam(1, $group_id, PDO::PARAM_INT);
    $stmt->execute();
}



header("Location: view_group_detail.php?group_id=$group_id");
exit();
?>
