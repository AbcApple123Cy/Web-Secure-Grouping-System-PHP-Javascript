<?php
require_once 'functions.php';


validateCookie($db);

// Check if group_id is specified in the URL parameters
if (!isset($_GET['group_id'])) {
    header("Location: home.php");
    exit();
}

$loginid = $_COOKIE['loggedin'];
  // Retrieve user information from database with Prepared Statements
  $user = retrieve($db, $loginid);
  
// Retrieve group information from database
$group_id = filter_var($_GET['group_id'], FILTER_SANITIZE_NUMBER_INT);
$stmt = $db->prepare("SELECT * FROM group_requests WHERE group_id = ?");
$stmt->execute([$group_id]);


$stmt = $db->prepare("SELECT * FROM group_members WHERE loginid = :loginid");

// bind the variable to the named sql placeholder
$stmt->bindParam(':loginid', $loginid, PDO::PARAM_STR);
$stmt->execute();

$group = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the user is a member of the group
$stmt = $db->prepare("SELECT * FROM group_members WHERE group_id = :group_id AND loginid = :loginid");
$stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
$stmt->bindParam(':loginid', $loginid, PDO::PARAM_STR);
$stmt->execute();
$group_member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$group_member) {
    // User is not a member of the group
    header("Location: view_group_detail.php?group_id=$group_id");
    exit();
}

// Remove user from the group
$stmt = $db->prepare("DELETE FROM group_members WHERE group_id = ? AND loginid = ?");
$stmt->execute([$group_id, $loginid]);

// Decrement group size
$stmt = $db->prepare("UPDATE group_requests SET current_size = current_size - 1 WHERE group_id = ?");
$stmt->execute([$group_id]);

header("Location: group_detail.php");
exit();
